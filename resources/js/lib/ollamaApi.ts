// Simple fetch wrapper for Ollama chat API endpoints
export async function sendOllamaMessage(message: string, conversation: Array<{role: string, content: string}> = []) {
    const response = await fetch('/api/ollama/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message, conversation }),
    });
    if (!response.ok) throw new Error('Failed to send message');
    return await response.json();
}
