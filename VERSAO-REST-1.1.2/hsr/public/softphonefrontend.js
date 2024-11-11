(function() {
    // Função para carregar scripts dinamicamente
    function loadScript(src, callback) {
        const script = document.createElement('script');
        script.src = src;
        script.onload = callback;
        document.head.appendChild(script);
    }

    // Carregar bibliotecas necessárias
    loadScript('https://cdn.jsdelivr.net/npm/webrtc-swarm@latest/dist/webrtc-swarm.min.js', () => {
        loadScript('https://cdn.jsdelivr.net/npm/sip.js@latest/dist/sip.min.js', () => {
            initializeSoftphone();
        });
    });

    // Função de inicialização do softphone
    function initializeSoftphone() {
        class Softphone extends EventEmitter {
            constructor() {
                super();
                this.sipClient = null;
                this.currentSession = null;
            }

            initialize(sipUsername, sipPassword, asteriskServerUrl) {
                this.sipClient = new SIP.UserAgent({
                    uri: SIP.UserAgent.makeURI(`sip:${sipUsername}@${asteriskServerUrl}`),
                    transportOptions: {
                        server: 'wss://fastdialer.fastquest.net:8089/ws'
                    },
                    authorizationUsername: sipUsername,
                    authorizationPassword: sipPassword,
                    sessionDescriptionHandlerFactoryOptions: {
                        constraints: {
                            audio: true,
                            video: false
                        }
                    }
                });

                this.sipClient.delegate = {
                    onRegistered: () => this.emit('Ready'),
                    onUnregistered: () => this.emit('Unregister'),
                    onInvite: (session) => this.handleIncomingCall(session),
                    onRegistrationFailed: (error) => this.emit('RegistrationFailed', error)
                };

                const registerer = new SIP.Registerer(this.sipClient);
                this.sipClient.start().then(() => registerer.register());
            }

            dial(to, campaign, anotation) {
                const targetURI = SIP.UserAgent.makeURI(`sip:${to}@fastdialer.fastquest.net`);
                const inviter = new SIP.Inviter(this.sipClient, targetURI, {
                    sessionDescriptionHandlerOptions: {
                        constraints: { audio: true, video: false }
                    }
                });

                inviter.invite().then((session) => {
                    this.currentSession = session;
                    this.emit('Calling', { call_id: session.id, to, campaign, anotation });
                    this.handleSession(session);
                }).catch((error) => this.emit('Failure', error));
            }

            hangup() {
                if (this.currentSession) {
                    this.currentSession.bye().then(() => {
                        this.emit('Hangup', { call_id: this.currentSession.id });
                        this.currentSession = null;
                    }).catch((error) => this.emit('Failure', error));
                }
            }

            pause(reason) {
                // Emitir evento de pausa
                this.emit('Pause', { reason, date: new Date() });
            }

            unpause() {
                // Emitir evento de saída de pausa
                this.emit('Unpause', { date: new Date() });
            }

            status() {
                // Emitir evento de status
                this.emit('Status', { status: 'current status' });
            }

            handleIncomingCall(session) {
                session.accept({
                    sessionDescriptionHandlerOptions: {
                        constraints: { audio: true, video: false }
                    }
                }).then(() => {
                    this.currentSession = session;
                    // Passar informações adicionais da chamada de entrada
                    const callDetails = {
                        call_id: session.id,
                        from: session.remoteIdentity.uri.toString(),
                        to: session.localIdentity.uri.toString(),
                        campaign: 'IncomingCampaign', // Exemplo de valor estático, adaptar conforme necessário
                        anotation: 'IncomingCall', // Exemplo de valor estático, adaptar conforme necessário
                        call_filename: 'filename_of_the_call',
                        call_type: 'INCOMING'
                    };
                    this.emit('Answer', callDetails);
                    this.handleSession(session);
                }).catch((error) => this.emit('Failure', error));

                session.on('bye', () => {
                    this.emit('Hangup', { call_id: session.id });
                    this.currentSession = null;
                });
            }

            handleSession(session) {
                const pc = session.sessionDescriptionHandler.peerConnection;

                pc.ontrack = (event) => this.handleIncomingAudioStream(event.streams[0]);

                pc.oniceconnectionstatechange = () => this.emit('Status', { state: pc.iceConnectionState });
                pc.onconnectionstatechange = () => this.emit('Status', { state: pc.connectionState });
            }

            handleIncomingAudioStream(stream) {
                const audioElement = document.createElement('audio');
                audioElement.srcObject = stream;
                audioElement.play().catch(error => this.emit('Failure', error));
            }
        }

        window.Softphone = new Softphone();
    }
})();
