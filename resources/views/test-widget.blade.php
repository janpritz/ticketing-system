<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Rasa Widget</title>

    <!-- Rasa Widget Styles -->
    <link rel="stylesheet" href="https://unpkg.com/@rasahq/chat-widget-ui/dist/rasa-chatwidget/rasa-chatwidget.css" />
    <!-- Rasa Widget Script -->
    <script type="module" src="https://unpkg.com/@rasahq/chat-widget-ui/dist/rasa-chatwidget/rasa-chatwidget.esm.js"></script>
</head>
<body>
    <h1>Rasa Chatbot Widget Test</h1>
    <rasa-chatbot-widget
        error-message="Server is not running. Please come again in a few minutes."
        widget-title="Sangkay Chatbot"
        server-url="{{ env('RASA_SERVER_URL') }}"
        bot-icon="{{ asset('logo-white.png') }}"
        initial-payload="Hi there! How can I assist you today?"
        stream-messages="true">
        <style>:root { --color-primary: #184c1c;}</style>
    </rasa-chatbot-widget>
</body>
</html>