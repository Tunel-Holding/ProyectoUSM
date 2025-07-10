window.addEventListener('load', function() {
    const grabarBtn = document.getElementById('record-btn');
    if (!grabarBtn) {
        console.error('No se encontró el botón de grabar (record-btn)');
        return;
    }
    let mediaRecorder;
    let recordedChunks = [];
    let stream;
    let grabando = false;

    const grabarIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>`;
    const detenerIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#d32f2f" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="3"/></svg>`;

    function actualizarBoton() {
        if (!grabando) {
            grabarBtn.innerHTML = grabarIcon + ' Grabar';
            grabarBtn.classList.remove('grabando');
        } else {
            grabarBtn.innerHTML = detenerIcon + ' Detener';
            grabarBtn.classList.add('grabando');
        }
    }

    actualizarBoton();

    grabarBtn.addEventListener('click', async () => {
        if (!grabando) {
            try {
                // Captura pantalla (video) y audio del sistema
                const displayStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });
                // Captura audio del micrófono
                let micStream = null;
                try {
                    micStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (e) {
                    // Si el usuario no da permiso al micrófono, solo se graba el audio del sistema
                }

                let tracks = [...displayStream.getVideoTracks()];
                let finalStream;

                // Mezcla los streams de audio si ambos existen
                const displayAudioTracks = displayStream.getAudioTracks();
                const micAudioTracks = micStream ? micStream.getAudioTracks() : [];

                if (displayAudioTracks.length > 0 && micAudioTracks.length > 0) {
                    // Mezclar ambos audios usando Web Audio API
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const destination = audioContext.createMediaStreamDestination();

                    // Fuente de audio de la pantalla
                    const displaySource = audioContext.createMediaStreamSource(new MediaStream(displayAudioTracks));
                    displaySource.connect(destination);

                    // Fuente de audio del micrófono
                    const micSource = audioContext.createMediaStreamSource(new MediaStream(micAudioTracks));
                    micSource.connect(destination);

                    // Combina video + audio mezclado
                    finalStream = new MediaStream([
                        ...tracks,
                        ...destination.stream.getAudioTracks()
                    ]);
                } else if (displayAudioTracks.length > 0) {
                    // Solo audio de la pantalla
                    finalStream = new MediaStream([
                        ...tracks,
                        ...displayAudioTracks
                    ]);
                } else if (micAudioTracks.length > 0) {
                    // Solo audio del micrófono
                    finalStream = new MediaStream([
                        ...tracks,
                        ...micAudioTracks
                    ]);
                } else {
                    // Solo video
                    finalStream = new MediaStream(tracks);
                }

                stream = finalStream;
                mediaRecorder = new MediaRecorder(stream);
                recordedChunks = [];

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    const blob = new Blob(recordedChunks, { type: 'video/webm' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'grabacion_pantalla.webm';
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(() => {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 100);
                };

                mediaRecorder.start();
                grabando = true;
                actualizarBoton();
            } catch (err) {
                alert('Error al intentar grabar la pantalla y el audio: ' + err);
            }
        } else {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                stream.getTracks().forEach(track => track.stop());
                grabando = false;
                actualizarBoton();
            }
        }
    });

    // Opcional: Cambia el color del botón cuando está grabando
    grabarBtn.classList.remove('grabando');
});