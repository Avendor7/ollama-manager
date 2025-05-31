<template>
    <div class="ai-composer">
        <textarea v-model="prompt" placeholder="Enter your prompt..." :disabled="loading" />
        <div class="controls">
            <button @click="generate" :disabled="loading">
                {{ loading ? 'Generating...' : 'Generate' }}
            </button>
            <button v-if="loading" @click="abort" class="abort-button">
                Stop
            </button>
        </div>
        <div ref="outputElement" class="output" v-html="output" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

// Reactive state
const prompt = ref<string>('')
const output = ref<string>('')
const loading = ref<boolean>(false)
const outputElement = ref<HTMLElement | null>(null)
let controller: AbortController | null = null

// Function to handle streamed response
const generate = async () => {
    if (loading.value) return
    if (!prompt.value.trim()) return

    // Reset state
    output.value = ''
    loading.value = true
    controller = new AbortController()

    try {
        const response = await fetch('/api/ai/stream-compose', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ prompt: prompt.value }),
            signal: controller.signal,
        })

        if (!response.body) throw new Error('No response body')

        const reader = response.body.getReader()
        const decoder = new TextDecoder()

        while (true) {
            const { value, done } = await reader.read()
            if (done) break

            const chunk = decoder.decode(value, { stream: true })
            try {
                // Parse as JSON stream (each chunk is a JSON object)
                const data = JSON.parse(chunk)
                output.value += data.content
                scrollToBottom()
            } catch (e) {
                console.error('Error parsing chunk:', e)
                output.value += chunk
                scrollToBottom()
            }
        }
    } catch (error: any) {
        if (error.name !== 'AbortError') {
            console.error('Stream error:', error)
            output.value += `<div class="error">Error: ${error.message}</div>`
        }
    } finally {
        loading.value = false
        controller = null
    }
}

// Function to abort ongoing request
const abort = () => {
    if (controller) {
        controller.abort()
        controller = null
        loading.value = false
    }
}

// Auto-scroll output to bottom
const scrollToBottom = () => {
    nextTick(() => {
        if (outputElement.value) {
            outputElement.value.scrollTop = outputElement.value.scrollHeight
        }
    })
}

// Clean up any ongoing requests when component unmounts
onBeforeUnmount(() => {
    if (controller) {
        controller.abort()
    }
})

// Optional: Typewriter effect using nextTick
import { nextTick } from 'vue'
</script>

<style scoped>
.ai-composer {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 12px;
    resize: vertical;
}

.controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

button {
    padding: 10px 20px;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.2s;
}

button:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

button.abort-button {
    background: #dc2626;
}

.output {
    min-height: 200px;
    max-height: 500px;
    overflow-y: auto;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f9fafb;
    white-space: pre-wrap;
    font-family: 'Inter', sans-serif;
    line-height: 1.6;
}

.error {
    color: #ef4444;
    font-weight: 500;
}
</style>
