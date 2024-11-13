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
            loadScript('https://cdn.jsdelivr.net/npm/webrtc-swarm@latest/dist/webrtc-swarm.min.js', () => {
                console.log("WebRTC Swarm loaded");
                loadScript('https://cdn.jsdelivr.net/npm/simple-peer@latest/simplepeer.min.js', () => {
                    console.log("Simple Peer loaded");
                    // Carregar softphonebackend.js
                    loadScript('https://fastdialer.fastquest.net/hsr/js/softphonebackend.js', () => {
                        console.log("softphonebackend.js loaded");
                        window.initializeSoftphone = initializeSoftphone;
                    });
                });
            });
        });
    });
})();

console.log('softphone.js loaded');

document.addEventListener('DOMContentLoaded', () => {
  let audioContext;
  let localStream;

  const form = document.getElementById('sip-credentials-form');
  const loginDiv = document.getElementById('login');
  const softphoneDiv = document.getElementById('softphone');
  const statusDiv = document.getElementById('status');
  const statusUnregisteredDiv = document.getElementById('status-unregistered');
  const dialpad = document.getElementById('dialpad');
  const sendButton = document.getElementById('send-button');
  const hangupButton = document.getElementById('hangup-button');
  const unregisterButton = document.getElementById('unregister-button');
  const pauseButton = document.getElementById('pause-button');
  const unpauseButton = document.getElementById('unpause-button');
  const apiLoginForm = document.getElementById('api-login-form');
  const apiLogoutButton = document.getElementById('api-logout-button');
  const callingNumberSpan = document.getElementById('calling-number');
  const callStatusSpan = document.getElementById('call-status');
  let currentSession;

  if (!form || !loginDiv || !softphoneDiv || !statusDiv || !statusUnregisteredDiv || !dialpad || !sendButton || !hangupButton || !unregisterButton) {
      console.error('One or more elements not found in the DOM.');
      return;
  }

  form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const sipUsername = document.getElementById('sip-username').value;
      const sipPassword = document.getElementById('sip-password').value;

      try {
          localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
      } catch (error) {
          console.error('Error accessing media devices.', error);
          return;
      }

      const peerConnection = new RTCPeerConnection({
          iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
          iceCandidatePoolSize: 10
      });

      const sipClient = new SIP.UserAgent({
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

      console.log('Initializing SIP client with:', {
          uri: `sip:${sipUsername}@fastdialer.fastquest.net`,
          server: 'wss://fastdialer.fastquest.net:8089/ws'
      });

      const registerer = new SIP.Registerer(sipClient);

      sipClient.start().then(() => {
          console.log('SIP client started');
          return registerer.register();
      }).then(() => {
          console.log('Sending SIP registration request');
      }).catch((error) => {
          console.error('Failed to start SIP client', error);
      });

      sipClient.delegate = {
          onRegistered: () => {
              console.log('SIP client registered successfully');
              statusDiv.style.display = 'block';
              statusUnregisteredDiv.style.display = 'none';
          },
          onUnregistered: () => {
              console.log('SIP client unregistered');
              statusDiv.style.display = 'none';
              statusUnregisteredDiv.style.display = 'block';
          },
          onInvite: (session) => {
              console.log('Received invite');
              handleIncomingCall(session);
          },
          onRegistrationFailed: (error) => {
              console.error('SIP client registration failed', error);
          }
      };

      dialpad.addEventListener('click', (event) => {
          const number = event.target.value;
          document.getElementById('number-input').value += number;
      });

      sendButton.addEventListener('click', () => {
        const number = document.getElementById('number-input').value;
        callingNumberSpan.textContent = number;
        callStatusSpan.textContent = 'Calling...';
        makeCall(number);
    });

      hangupButton.addEventListener('click', () => {
          if (currentSession) {
              hangupCall(currentSession);
          }
      });

      unregisterButton.addEventListener('click', () => {
          registerer.unregister().then(() => {
              console.log('SIP client unregistered');
              statusDiv.style.display = 'none';
              statusUnregisteredDiv.style.display = 'block';
          }).catch((error) => {
              console.error('Failed to unregister SIP client', error);
          });
      });

// Evento de login da API
apiLoginForm.addEventListener('submit', (event) => {
  event.preventDefault();
  const apiUsername = document.getElementById('api-username').value;
  const apiAltUsername = document.getElementById('api-alt-username').value;

  fetch('https://fastdialer.fastquest.net/hsr/api/dialer/login', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({
          user: apiUsername,
          alt_user: apiAltUsername
      })
  })
  .then(response => response.json())
  .then(data => {
      if (data.result === 'SUCCESS') {
          console.log('API login successful:', data);
          // Atualizar o status do usuário no frontend
          statusDiv.style.display = 'block';
          statusUnregisteredDiv.style.display = 'none';
      } else {
          console.error('API login failed:', data);
      }
  })
  .catch((error) => {
      console.error('Error during API login:', error);
  });
});

// Evento de logout da API
apiLogoutButton.addEventListener('click', () => {
  const apiUsername = document.getElementById('api-username').value;

  fetch('https://fastdialer.fastquest.net/hsr/api/dialer/logout', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({
          user: apiUsername
      })
  })
  .then(response => response.json())
  .then(data => {
      if (data.result === 'SUCCESS') {
          console.log('API logout successful:', data);
          // Atualizar o status do usuário no frontend
          statusDiv.style.display = 'none';
          statusUnregisteredDiv.style.display = 'block';
      } else {
          console.error('API logout failed:', data);
      }
  })
  .catch((error) => {
      console.error('Error during API logout:', error);
  });
});


      // Set up pause button functionality
      pauseButton.addEventListener('click', () => {
          if (currentSession) {
              pauseCall(currentSession, 'User requested pause');
          }
      });

      // Set up unpause button functionality
      unpauseButton.addEventListener('click', () => {
          if (currentSession) {
              unpauseCall(currentSession);
          }
      });

      function makeCall(number) {
        const targetURI = SIP.UserAgent.makeURI(`sip:${number}@fastdialer.fastquest.net`);
        if (!targetURI) {
            console.error('Invalid target URI');
            return;
        }
    
        const inviter = new SIP.Inviter(sipClient, targetURI, {
            sessionDescriptionHandlerOptions: {
                constraints: { audio: true, video: false }
            }
        });
    
        inviter.invite().then((session) => {
            console.log('Call started', session);
            currentSession = session;
            softphone.emit('Calling', { call_id: session.id, to: number });
            callStatusSpan.textContent = 'In Call';
            handleSession(session);
        }).catch((error) => {
            console.error('Failed to make call', error);
            softphone.emit('Failure', error);
            callStatusSpan.textContent = 'Call Failed';
        });
    }
    function hangupCall(session) {
      session.bye().then(() => {
          console.log('Call ended');
          currentSession = null;
          softphone.emit('Hangup', { call_id: session.id });
          callStatusSpan.textContent = 'Call Ended';
      }).catch((error) => {
          console.error('Failed to hang up call', error);
          softphone.emit('Failure', error);
          callStatusSpan.textContent = 'Hang Up Failed';
      });
  }
  
  // No recebimento de uma chamada
  function handleIncomingCall(session) {
      console.log('Handling incoming call', session);
      session.accept({
          sessionDescriptionHandlerOptions: {
              constraints: { audio: true, video: false }
          }
      }).then(() => {
          console.log('Call accepted', session);
          currentSession = session;
          softphone.emit('Answer', { call_id: session.id, from: session.remoteIdentity.uri.toString() });
          callStatusSpan.textContent = 'In Call';
          handleSession(session);
      }).catch((error) => {
          console.error('Failed to accept call', error);
          softphone.emit('Failure', error);
          callStatusSpan.textContent = 'Call Failed';
      });
  
      session.on('bye', () => {
          console.log('Call ended by remote');
          currentSession = null;
          softphone.emit('Hangup', { call_id: session.id });
          callStatusSpan.textContent = 'Call Ended';
      });
  }
  

      function handleSession(session) {
          console.log('Session:', session);
          console.log('Session description handler:', session.sessionDescriptionHandler);
          console.log('Peer connection:', session.sessionDescriptionHandler.peerConnection);
          if (!session.sessionDescriptionHandler || !session.sessionDescriptionHandler.peerConnection) {
              console.error('Session description handler or peer connection is undefined.');
              return;
          }

          const pc = session.sessionDescriptionHandler.peerConnection;

          if (localStream) {
              console.log('Adding local audio tracks to peer connection.');
              localStream.getTracks().forEach(track => {
                  pc.addTrack(track, localStream);
              });
          } else {
              console.error('Local stream is not available.');
          }

          pc.ontrack = (event) => {
              console.log('Received audio stream', event.streams);
              handleIncomingAudioStream(event.streams[0]);
          };

          pc.oniceconnectionstatechange = () => {
              console.log('ICE Connection State changed to', pc.iceConnectionState);
          };

          pc.onconnectionstatechange = () => {
              console.log('Connection State changed to', pc.connectionState);
          };
      }

      function handleIncomingAudioStream(stream) {
        console.log('Handling incoming audio stream:', stream);
        const audioElement = document.createElement('audio');
        audioElement.srcObject = stream;
        audioElement.play().catch(error => {
          console.error('Error playing audio stream', error);
        });
      }
      
  });
});
