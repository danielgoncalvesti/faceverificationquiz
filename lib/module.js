/**
 * Adding the flash container to view page also this will try to update img.profilepic
 * Added support for detecting webrtc most modern browser will support this
 *
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package faceverificationquiz
 * @copyright  2020 Daniel Gonçalves da Silva <danielgoncalvesti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
// amd module.
M.faceverificationquiz = {
    /**
     * Load face models.
     */
    loadmodel: async function(){
        // console.log(options.face_registered);
        console.log("model carregada");
        // await faceapi.loadMtcnnModel('../models');
        await faceapi.nets.tinyFaceDetector.loadFromUri('accessrule/faceverificationquiz/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('accessrule/faceverificationquiz/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('accessrule/faceverificationquiz/models');
        // await faceapi.nets.faceExpressionNet.loadFromUri('../models');
        // await faceapi.nets.ssdMobilenetv1.load('accessrule/faceverificationquiz/models');
        console.log("model carregada");
    },

    /**
     * Logging.
     * @param val
     */
    log: function(val) {
        try {
            // console.log(val);
        } catch (e) {

        }
    },

    /**
     * Init
     *
     * @param Y
     * @param applicationpath
     * @param expresspath
     * @param options
     * @param supportwebrtc
     */
    init: function(Y, applicationpath, expresspath, options) {
        var startAttmptButton = document.querySelector('#id_submitbutton');
        var cancelButton = document.querySelector('#id_cancel');
        startAttmptButton.style.visibility = 'hidden';
        cancelButton.style.visibility = 'hidden';

        // if (location.protocol != 'https:') {
        //     alert('Microphone and Camera access no longer works on insecure origins. ' +
        //         'To use this feature, you should consider switching your application to a secure origin, ' +
        //         'such as HTTPS. See https://goo.gl/rStTGz for more details.');
        //         console.log("aqui");
        // }

        // if (this.webrtc_is_supported() === false) {
        //     alert('WebRTC is not supported');
        //     return;
        // }
        // console.log(options.type_of_access);
        this.loadmodel();
        M.faceverificationquiz.log('We have support for Webrtc');
        Y.one('#snapshotholder_webrtc').setStyle('display', 'block');

        this.webrtc_load(options);
    },

    /**
     *
     * @param options
     */
    webrtc_load: function(options) {
        var _this = this;
        var index = 0;
        var labeledDescriptors = [];
        var featuresValues;
        var snapshotButton = document.querySelector('button#snapshot');
        var video = window.video = document.querySelector('video');

        var canvasFrame = document.querySelector("#canvasFrame");

        // canvas render ->  desenha as localizacoes das features
        var canvasrender = window.canvas = document.querySelector('canvas#render');
        console.log("canvas");
        console.log(isCanvasBlank(canvasrender));
        console.log("canvas");
        var canvaspreview = window.canvas = document.querySelector('canvas#preview');

        var detectionCopy = null;
        var canvasRenderReady = false;
        const mtcnnForwardParams = {
            // limiting the search space to larger faces for webcam detection
            minFaceSize: 200
        }

        function isCanvasBlank(canvas) {
            const context = canvas.getContext('2d');
            const pixelBuffer = new Uint32Array(
              context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
            );
            return !pixelBuffer.some(color => color !== 0);
          }        

        snapshotButton.onclick = function(event) {
            if (detectionCopy === undefined || detectionCopy === null || typeof detectionCopy[0] === 'undefined') {
                event.preventDefault();
                alert("Face não localizada! Tente novamente.");
            } else {
                console.log(video);
                canvasFrame.getContext('2d').drawImage(video, 0, 0, canvasFrame.width, canvasFrame.height);
                var ctxfc = canvaspreview.getContext("2d");
                console.log(detectionCopy[0]);

                // var croppedImage = canvasFrame.getContext("2d").getImageData(detectionCopy[0].alignedRect.box.x, detectionCopy[0].alignedRect.box.y, detectionCopy[0].alignedRect.box.width, detectionCopy[0].alignedRect.box.height);
                var croppedImage = canvasFrame.getContext("2d").getImageData(detectionCopy[0].detection.box.x, detectionCopy[0].detection.box.y, detectionCopy[0].detection.box.width, detectionCopy[0].detection.box.height);
                
                ctxfc.clearRect(0, 0, canvaspreview.width, canvaspreview.height+800);
                ctxfc.beginPath();
                ctxfc.putImageData(croppedImage, 0, 0);

                var canvas = document.getElementById('preview'),
                    dataUrl = dataUrl = canvas.toDataURL(),
                    imageFoo = document.getElementById('img_preview');
                imageFoo.src = dataUrl;
                var data = canvasFrame.toDataURL('image/png');

                // console.log(detectionCopy[0]);            

                if (options.type_of_access == 'verification'){
                    console.log("cadastrado:");
                    console.log(options.face_registered.facevalues);
                    console.log("atual: "+detectionCopy[0].descriptor);
    
                    face_registed_float32 =  new Float32Array(options.face_registered.facevalues.split(','));

                    captured_face_float32 = new Float32Array(detectionCopy[0].descriptor.toString().split(','));

                    const euclideanDist =  faceapi.euclideanDistance(face_registed_float32, captured_face_float32);
                    console.log(euclideanDist);
                    
                    if (euclideanDist < 0.60){
                        YUI().use('io-base', function(Y) {
                            // Saving the file.
                            var cfg = {
                                method: 'POST',
                                data: {
                                    'quizid': options.quizid,
                                    'courseid' : options.courseid,
                                    'euclidean_distance': euclideanDist,
                                    'sesskey': options.sessionid,         
                                    'descriptor': detectionCopy[0].descriptor.toString(), 
                                    'facedetectionscore': detectionCopy[0].detection.score.toString(),
                                    'file': data
                                }
                            };
                            var request = Y.io(options.faceverificationPath, cfg);
        
                            // On completed request.
                            Y.on('io:complete', onComplete, Y);
                        });
                        console.log(isCanvasBlank(canvasrender));
                        alert("Identidade verificada com sucesso! \nScore de semelhança: "+ euclideanDist.toFixed(2)) 
                    } else {
                        alert("Não foi possível verificar sua identidade. \nScore de semelhança: "+ euclideanDist.toFixed(2));
                    }
                    
                    
                } else {
                    YUI().use('io-base', function(Y) {
                        // Saving the file.
                        var cfg = {
                            method: 'POST',
                            data: {
                                'descriptor': detectionCopy[0].descriptor.toString(),
                                'sesskey': options.sessionid,
                                'file': data   
                            }
                        };
                        var request = Y.io(options.uploadPath, cfg);

                        // On completed request.
                        Y.on('io:complete', onComplete, Y);
                    }); 
                    alert("Foto cadastrada com sucesso!");
                }
            }
        };

        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;

        var constraints = {
            audio: false,
            "video": {
                "mandatory": {
                    "minWidth": "480",
                    "minHeight": "320",
                    "minFrameRate": "30",
                    "minAspectRatio": "1",
                    "maxWidth": "1280",
                    "maxHeight": "720",
                    "maxFrameRate": "30",
                    "maxAspectRatio": "2"
                },
                "optional": []
            }
        };

        /**
         *
         * @param stream
         */
        function successCallback(stream) {

            window.stream = stream; // make stream available to browser console
            if (window.URL) {
                try {
                    video.srcObject = stream;
                } catch (e) {
                    video.src = window.URL.createObjectURL(stream);
                    
                }
            } else {
                video.srcObject = stream;
            }
        }

        video.addEventListener('play', () => {
            const displaySize = { width: 480, height: 320};
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors();
                // const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceDescriptors();
                // const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options()).withFaceDescriptors();
                // const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceDescriptors();
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                detectionCopy = resizedDetections;
                canvasrender.getContext('2d').clearRect(0, 0, 480, 320);
                faceapi.draw.drawDetections(canvasrender, resizedDetections);
                // faceapi.draw.drawFaceLandmarks(canvasrender, resizedDetections);
                // faceapi.draw.drawFaceExpressions(canvasrender, resizedDetections);
                if (canvasRenderReady == false){
                    console.log("loading...");
                    if (!isCanvasBlank(canvasrender)){
                        canvasRenderReady = true;
                        console.log("ready!");
                        var cssloader = document.getElementById("cssloader");
                        var btnSnapshot = document.getElementById("snapshot");
                        var videostreaming = document.getElementById("videostreaming");
                        videostreaming.style.opacity = "1";
                        btnSnapshot.style.display = "block";
                        cssloader.remove();

                    } else {
                        console.log("loading...");
                    }
                } else {
                    console.log("ready!");
                }
            }, 100);
        });

        /**
         * onComplete
         *
         * @param transactionid
         * @param response
         * @param arguments
         */
        function onComplete(transactionid, response, arguments) {
            try {
                console.log(response);
                console.log("salva json");
                var json = JSON.parse(response.response);
                console.log("------JSON-----");
                console.log(json);
                console.log("------JSON-----");
                if (json.status == true) {
                    // Reload profile picture.
                    M.faceverificationquiz.saved();
                }
                M.faceverificationquiz.log(json);
            } catch (exc) {
                console.log(json);
                console.log(exc);
            }
        }
        function errorCallback(error) {
            console.log('navigator.getUserMedia error: ', error);
        }
        navigator.getUserMedia(constraints, successCallback, errorCallback);
        
    },

    /**
     * Check if webrtc is supported.
     * HTTPS also needed to be enabled.
     *
     * @returns {boolean}
     */
    webrtc_is_supported: function() {
        return !!(navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia) && location.protocol == 'https:';
    },

    /**
     * Called when profile picture is saved.
     */
    saved: function() {
        this.log('The picture was saved!');
        var profilePicture = Y.one('img.profilepic');
        console.log(profilePicture);
        if (profilePicture) {
            var src = profilePicture.getAttribute('src');
            profilePicture.setAttribute('src', '');
            setTimeout(function() {
                var now = new Date().getTime() / 1000;
                profilePicture.setAttribute('src', src + '&c=' + now);
            }, 500);

        }
    },

    /**
     * Error message.
     * @param err
     */
    error: function(err) {
        M.faceverificationquiz.log('Error!');
        M.faceverificationquiz.log(err);
    }
};
