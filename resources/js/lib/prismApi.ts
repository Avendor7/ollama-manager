export async function sendPrismOllamaMessage(message: string, conversation: Array<{role: string, content: string}> = []) {
    const response = await fetch('/api/prism/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message, conversation }),
    });
    if (!response.ok) throw new Error('Failed to send message');
    return await response.json();
}
