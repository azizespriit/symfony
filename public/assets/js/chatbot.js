document.addEventListener('DOMContentLoaded', () => {
    const chatbotButton = document.getElementById('chatbot-button');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');

    chatbotButton.addEventListener('click', () => {
        chatbotWindow.style.display = chatbotWindow.style.display === 'none' ? 'block' : 'none';
    });

    const sendMessage = async () => {
        const userMessage = chatbotInput.value.trim();
        if (!userMessage) return;

        chatbotMessages.innerHTML += `<div><b>Moi:</b> ${userMessage}</div>`;
        chatbotInput.value = '';

        try {
            const response = await fetch('/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(userMessage)}`
            });

            if (!response.ok) {
                chatbotMessages.innerHTML += `<div style="color: red;">Erreur lors de la communication avec l'assistant (Code ${response.status}).</div>`;
            } else {
                const data = await response.json();
                chatbotMessages.innerHTML += `<div><b>Assistant:</b> ${data.reply}</div>`;
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        } catch (error) {
            chatbotMessages.innerHTML += `<div style="color: red;">Erreur de communication.</div>`;
        }
    };

    chatbotInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
