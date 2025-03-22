document.addEventListener('DOMContentLoaded', function() {
    // Create and animate confetti elements
    const confettiContainer = document.querySelector('.confetti');
    const colors = ['#4e54c8', '#8f94fb', '#26d07c', '#5ddcff', '#ff7676'];
    
    // Create 50 confetti elements
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'absolute';
        confetti.style.width = Math.random() * 10 + 5 + 'px';
        confetti.style.height = Math.random() * 10 + 5 + 'px';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.top = '-10%';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.opacity = Math.random() + 0.4;
        confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
        
        // Set animation
        const duration = Math.random() * 3 + 2;
        const delay = Math.random() * 2;
        
        confetti.style.animation = `confettiFall ${duration}s ease-in ${delay}s forwards`;
        
        // Add keyframe animation
        const keyframes = `
            @keyframes confettiFall {
                0% {
                    transform: translateY(0) rotate(${Math.random() * 360}deg);
                    opacity: ${Math.random() + 0.4};
                }
                100% {
                    transform: translateY(${window.innerHeight}px) rotate(${Math.random() * 720}deg);
                    opacity: 0;
                }
            }
        `;
        
        const styleElement = document.createElement('style');
        styleElement.innerHTML = keyframes;
        document.head.appendChild(styleElement);
        
        confettiContainer.appendChild(confetti);
    }
    
    // Contact button functionality
    document.getElementById('contactBtn').addEventListener('click', function(e) {
        e.preventDefault();
        // You could open a mailto: link or a contact form modal
        window.location.href = 'mailto:support@edxtratech.com';
    });
    
    // Display the submitted form data (optional)
    function displayFormData() {
        const formData = sessionStorage.getItem('formData');
        if (formData) {
            try {
                const data = JSON.parse(formData);
                const nameEl = document.querySelector('.message');
                // Personalize the message with the user's name
                if (data.name) {
                    nameEl.textContent = `Thank you, ${data.name}! Your response has been successfully submitted.`;
                }
            } catch (e) {
                console.log('Error parsing form data', e);
            }
        }
    }
    
    // Play success sound with improved reliability
    function playSuccessSound() {
        // Create audio element
        const audio = new Audio();
        
        // Try multiple audio formats for better browser compatibility
        const audioSources = [
            // MP3 data URI
            'data:audio/mp3;base64,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',
            // Alternative audio URL
            'https://www.soundjay.com/buttons/sounds/button-37.mp3',
            // Fallback audio URL
            'https://soundbible.com/mp3/Robot_blip-Marianne_Gagnon-120342607.mp3'
        ];
        
        // Try each audio source until one works
        function tryNextSource(index) {
            if (index >= audioSources.length) {
                console.log('All audio sources failed');
                return;
            }
            
            audio.src = audioSources[index];
            
            audio.oncanplaythrough = function() {
                try {
                    audio.play()
                        .then(function() {
                            console.log('Audio playback started successfully');
                        })
                        .catch(function(error) {
                            console.log('Audio playback failed:', error);
                            tryNextSource(index + 1);
                        });
                } catch (e) {
                    console.log('Error playing audio:', e);
                    tryNextSource(index + 1);
                }
            };
            
            audio.onerror = function() {
                console.log('Audio source error, trying next source');
                tryNextSource(index + 1);
            };
        }
        
        // Start trying to play audio
        tryNextSource(0);
    }
    
    // Create a user interaction to trigger sound
    function setupAudioTrigger() {
        // Create a transparent button overlay that responds to any click on the page
        const audioTrigger = document.createElement('div');
        audioTrigger.style.position = 'fixed';
        audioTrigger.style.top = '0';
        audioTrigger.style.left = '0';
        audioTrigger.style.width = '100%';
        audioTrigger.style.height = '100%';
        audioTrigger.style.backgroundColor = 'transparent';
        audioTrigger.style.zIndex = '9999';
        audioTrigger.style.cursor = 'pointer';
        
        document.body.appendChild(audioTrigger);
        
        // Add notification to click anywhere
        const notification = document.createElement('div');
        notification.textContent = 'Click anywhere to continue';
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.left = '50%';
        notification.style.transform = 'translateX(-50%)';
        notification.style.padding = '10px 20px';
        notification.style.backgroundColor = 'rgba(0,0,0,0.7)';
        notification.style.color = 'white';
        notification.style.borderRadius = '20px';
        notification.style.fontSize = '14px';
        
        audioTrigger.appendChild(notification);
        
        // Play sound on click
        audioTrigger.addEventListener('click', function() {
            playSuccessSound();
            document.body.removeChild(audioTrigger);
        });
    }
    
    // Create an audio element and attach it directly to the page
    function createFallbackAudioElement() {
        const audioContainer = document.createElement('div');
        audioContainer.innerHTML = `
            <audio autoplay controls style="display:none">
                <source src="https://www.soundjay.com/buttons/sounds/button-37.mp3" type="audio/mpeg">
                <source src="https://soundbible.com/mp3/Robot_blip-Marianne_Gagnon-120342607.mp3" type="audio/mpeg">
            </audio>
        `;
        document.body.appendChild(audioContainer);
    }
    
    // Attempt all methods to play the success sound
    setTimeout(function() {
        playSuccessSound();
        createFallbackAudioElement();
        setupAudioTrigger();
        
        // Display form data
        displayFormData();
    }, 500);
});
