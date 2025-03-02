let chat;
let typing;

document.addEventListener('DOMContentLoaded', () => {
    typing = document.querySelector(".typing_form");
    chat = document.querySelector(".chatlist");

    if (typing && chat) {
        typing.addEventListener("submit", (e) => {
            e.preventDefault();
            handleoutgoingchat();
        });
    } else {
        console.error('Required elements not found in the DOM');
    }
});

let usermessage = "";

const API_KEY = "AIzaSyDn8qqEXZwx-0xktYb5kyPXB_qp5cRMH7g";
const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${API_KEY}`;

const systemContext = `You are an AI sales optimization expert. When users provide equipment details (type, quantity, and price), 
provide strategic recommendations to increase sales and customer engagement. Focus on:
1. Market positioning and pricing strategy
2. Customer targeting and value proposition
3. Sales channels optimization
4. Marketing tactics and promotional strategies
5. Inventory management suggestions
Provide detailed, actionable advice that can be implemented immediately.`;

const generateapireponse = async (div) => {
    const textElement = div.querySelector(".text");
    if (!textElement) {
        console.error("Text element not found in the message div");
        return;
    }
    try {
        const response = await fetch(API_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                contents: [{
                    role: "user",
                    parts: [{
                        text: `${systemContext}\n\nAnalyze and provide recommendations for: ${usermessage}`
                    }]
                }]
            })
        });
        const data = await response.json();
        const apiresponse = data.candidates[0].content.parts[0].text;
        textElement.innerHTML = apiresponse.replace(/\n/g, '<br>');
    } catch(error) {
        console.error('Error generating recommendations:', error);
        textElement.innerHTML = 'Sorry, I encountered an error while generating recommendations. Please try again.';
    }
}

const showloading = () => {
    const html = `
           <div class="message_content">
                    <i class="fas fa-robot" style="font-size: 24px; margin-right: 10px;"></i>
                    <p class="text">Generating recommendations...</p>
            </div>
    `
    const div = document.createElement("div");
    div.classList.add("message", "bot-message");
    div.innerHTML = html;
    chat.appendChild(div);

    generateapireponse(div);
}

const handleoutgoingchat = () => {
    usermessage = document.querySelector(".typing_input").value;
    if(!usermessage) return;
    const html = `
            <div class="message_content">
                    <i class="fas fa-user" style="font-size: 24px; margin-right: 10px;"></i>
                    <p class="text">${usermessage}</p>
            </div>
    `
    const div = document.createElement("div");
    div.classList.add("message", "user-message");
    div.innerHTML = html;
    chat.appendChild(div);
    typing.reset();
    setTimeout(showloading, 500);
}
