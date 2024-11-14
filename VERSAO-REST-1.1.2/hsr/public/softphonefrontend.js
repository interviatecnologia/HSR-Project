console.log("softphonefrontend.js loaded");

(function() {
    console.log("softphonefrontend.js script started");

    // Função para carregar scripts dinamicamente
    function loadScript(src, callback) {
        const script = document.createElement('script');
        script.src = src;
        script.onload = callback;
        script.onerror = function() { console.error(`Error loading script: ${src}`); };
        document.head.appendChild(script);
    }

    // Carregar bibliotecas necessárias
    loadScript('https://cdn.jsdelivr.net/npm/eventemitter3@latest/index.min.js', () => {
        console.log("EventEmitter3 loaded");
        loadScript('https://cdn.jsdelivr.net/npm/sip.js@latest/dist/sip.min.js', () => {
            console.log("SIP.js loaded");
            loadScript('https://cdn.jsdelivr.net/npm/simple-peer@latest/simplepeer.min.js', () => {
                console.log("Simple Peer loaded");
                // Aqui é onde você cria a instância do Softphone
                window.Softphone = new Softphone(); 

                // Registro de eventos para feedback no console
    window.Softphone.eventEmitter.on('Register', (event) => console.log('Register event:', event));
    window.Softphone.eventEmitter.on('Unregister', (event) => console.log('Unregister event:', event));
    window.Softphone.eventEmitter.on('Calling', (event) => console.log('Calling event:', event));
    window.Softphone.eventEmitter.on('Answer', (event) => console.log('Answer event:', event));
    window.Softphone.eventEmitter.on('Hangup', (event) => console.log('Hangup event:', event));
    window.Softphone.eventEmitter.on('Failure', (event) => console.log('Failure event:', event));
    window.Softphone.eventEmitter.on('Pause', (event) => console.log('Pause event:', event));
    window.Softphone.eventEmitter.on('Unpause', (event) => console.log('Unpause event:', event));
    window.Softphone.eventEmitter.on('Status', (event) => console.log('Status event:', event));
    window.Softphone.eventEmitter.on('Ready', (event) => console.log('Ready event:', event));
    
});
            });
        });
    });

class Softphone {
    constructor() {
        this.audioContext = null;
        this.localStream = null;
        this.sipClient = null;
        this.currentSession = null;
        this.registerer = null;
        this.eventEmitter = new EventEmitter(); // Agora esta linha funcionará
        this.paused = false; // Para controle de pausa
    }

    async initialize(sipUsername, sipPassword) {  
        console.log('Initializing SIP Client with:', sipUsername, sipPassword);  
        
        try {  
         this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });  
        } catch (error) {  
         console.error('Error accessing media devices.', error);  
         return;  
        }  
        
        this.sipClient = new SIP.UserAgent({  
         uri: SIP.UserAgent.makeURI(`sip:${sipUsername}@fastdialer.fastquest.net`),  
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
        
        this.sipClient.start().then(() => {  
         console.log('SIP client started');  
         this.registerer = new SIP.Registerer(this.sipClient);  
         this.registerer.register(this.sipClient, {  
          type: 'REGISTER',  
          headers: { 'Authorization': 'Basic ' + btoa(`${sipUsername}:${sipPassword}`) },  
          timeout: 30000,  
          retry: 3  
         }).then(() => {  
          console.log('SIP client registered successfully');  
         }).catch((error) => {  
          console.error('Failed to register SIP client', error);  
         });  
        }).catch((error) => {  
         console.error('Failed to start SIP client', error);  
        });  
      
       
      
        this.sipClient.delegate = {
            onRegistered: () => {
                console.log('SIP client registered successfully');
                this.eventEmitter.emit('Status', { state: 'registered' });
            },
            onUnregistered: () => {
                console.log('SIP client unregistered');
                this.eventEmitter.emit('Status', { state: 'unregistered' });
            },
            onInvite: (session) => {
                console.log('Received invite');
                this.handleIncomingCall(session);
            },
            onRegistrationFailed: (error) => {  
                console.error('SIP client registration failed', error);  
                this.eventEmitter.emit('Failure', error);  
               }  
             };
    }

    
    makeCall(number) {
        const targetURI = SIP.UserAgent.makeURI(`sip:${number}@fastdialer.fastquest.net`);
        if (!targetURI) {
            console.error('Invalid target URI');
            return;
        }

        const inviter = new SIP.Inviter(this.sipClient, targetURI, {
            sessionDescriptionHandlerOptions: {
                constraints: { audio: true, video: false }
            }
        });

        inviter.invite().then((session) => {
            console.log('Call started', session);
            this.currentSession = session;
            this.handleSession(session);
        }).catch((error) => {
            console.error('Failed to make call', error);
        });
    }

    hangupCall() {
        if (this.currentSession) {
            this.currentSession.bye().then(() => {
                console.log('Call ended');
                this.eventEmitter.emit('Hangup', { call_id: this.currentSession.id });
                this.currentSession = null;
            }).catch((error) => {
                console.error('Failed to hang up call', error);
            });
        }
    }

    handleIncomingCall(session) {
        session.accept({
            sessionDescriptionHandlerOptions: {
                constraints: { audio: true, video: false }
            }
        }).then(() => {
            this.currentSession = session;
            const callDetails = {
                call_id: session.id,
                from: session.remoteIdentity.uri.toString(),
                to: session.localIdentity.uri.toString(),
                campaign: 'IncomingCampaign',
                anotation: 'IncomingCall',
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

        pc.oniceconnectionstatechange = () => {
            this.eventEmitter.emit('Status', { state: pc.iceConnectionState });
        };
        pc.onconnectionstatechange = () => {
            this.eventEmitter.emit('Status', { state: pc.connectionState });
        };
    }

    handleIncomingAudioStream(stream) {
        const audioElement = document.createElement('audio');
        audioElement.srcObject = stream;
        audioElement.play().catch(error => {
            console.error('Error playing audio stream:', error);
            this.eventEmitter.emit('Failure', error);
        });
    }
   
}

// Adicione um listener para o evento DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    // Exemplo de como usar a classe Softphone
    document.getElementById('start-button').addEventListener('click', () => {
        const sipUsername = document.getElementById('sip-username').value;
        const sipPassword = document.getElementById('sip-password').value;

        window.Softphone.initialize(sipUsername, sipPassword);
    });

    // Adicionando funcionalidade para o botão de chamada
    document.getElementById('send-button').addEventListener('click', () => {
        const number = document.getElementById('number-input').value;
        window.Softphone.makeCall(number); // Corrigido para usar makeCall
    });

    // Adicionando funcionalidade para o botão de desligar
    document.getElementById('hangup-button').addEventListener('click', () => {
        window.Softphone.hangupCall(); // Corrigido para usar hangupCall
    });

    // Adicionando funcionalidade para o botão de registrar/desregistrar
    document.getElementById('unregister-button').addEventListener('click', () => {
        if (window.Softphone.registerer) {
            window.Softphone.registerer.unregister().then(() => {
                console.log('SIP client unregistered');
                window.Softphone.eventEmitter.emit('Unregister'); // Corrigido para usar eventEmitter
            }).catch((error) => {
                console.error('Failed to unregister SIP client', error);
            });
        }
    });

    // Adicionando funcionalidade para o botão de pausa
    document.getElementById('pause-button').addEventListener('click', () => {
        window.Softphone.pause('User  requested pause');
    });

    // Adicionando funcionalidade para o botão de despausa
    document.getElementById('unpause-button').addEventListener('click', () => {
        window.Softphone.unpause();
    });

});
