<rasa-chatbot-widget 
    error-message="Server is not running. Please come again in a few minutes."
    widget-title="Sangkay Chatbot" 
    server-url="{{ env('RASA_SERVER_URL') }}" 
    bot-icon="{{ asset('logo-white.png') }}"
    initial-payload="Hi there! How can I assist you today?" 
    stream-messages="true">
    <style>
        :root {
            --color-primary: #184c1c;
        }
        rasa-chatbot-widget {
            height: 300px;
        }
    </style>
</rasa-chatbot-widget>