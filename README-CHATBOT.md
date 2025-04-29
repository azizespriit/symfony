# AI Chatbot Integration

This project integrates an AI-powered chatbot using the OpenAI API. The chatbot can help users with questions about your sports services, provide assistance, and handle inquiries.

## Setup Instructions

### 1. Configure Environment Variables

Update your `.env.local` file (create it if it doesn't exist) with your OpenAI API credentials:

```
CHATBOT_API_KEY=your_actual_api_key_here
CHATBOT_API_ENDPOINT=https://api.openai.com/v1/chat/completions
```

Replace `your_actual_api_key_here` with your actual OpenAI API key.

### 2. Install Required Dependencies

Ensure you have the Symfony HTTP Client component installed:

```bash
composer require symfony/http-client
```

### 3. Clear Cache

Clear the cache to apply the new configuration:

```bash
symfony console cache:clear
```

## Usage

### Accessing the Chatbot

The chatbot is only accessible for authenticated users from the front-end interface:

1. **Chat bubble**: A floating chat bubble is displayed on the front-end pages (using the basefront.html.twig template). Clicking it takes users to the chatbot interface.
2. **Direct URL**: Navigate to `/front/chatbot` when logged in to access the chatbot interface directly.

The chatbot requires user authentication (ROLE_USER) and is integrated specifically with the front-end theme.

### Admin Configuration (Future Enhancement)

Future versions will include an admin interface to:
- Customize the chatbot's initial greeting
- Add domain-specific knowledge
- View conversation logs
- Set up automated responses for common questions

## Customization

### Styling

You can customize the chatbot appearance by modifying the CSS in:
- `templates/chatbot/index.html.twig` - For the main chatbot interface
- `templates/basefront.html.twig` - For the floating chat button

### Behavior

To modify the chatbot's behavior or capabilities:

1. Edit `src/Service/ChatbotService.php` to change API parameters or response handling
2. Update `src/Controller/ChatbotController.php` to add new features or endpoints

## Troubleshooting

If the chatbot isn't working properly:

1. Check the logs: `var/log/dev.log`
2. Verify that your API key is correct and has sufficient credits
3. Ensure the API endpoint is accessible from your server
4. Check browser console for JavaScript errors
5. Verify the user has proper authentication (ROLE_USER)

## Security Considerations

- The API key is stored as an environment variable and should be kept secure
- User conversations are not stored persistently in the current implementation
- Consider adding rate limiting to prevent abuse
- Access is restricted to authenticated users only 