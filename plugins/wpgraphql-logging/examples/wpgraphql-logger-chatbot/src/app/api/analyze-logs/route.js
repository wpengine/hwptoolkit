import { NextResponse } from 'next/server';
// import OpenAI from 'openai'; // Commented out OpenAI
import { GoogleGenerativeAI } from '@google/generative-ai';

export async function POST() {
  // Initialize the Google Gemini client
  const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

  // Define the model to use

  const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash"});

  const wordpressUrl = process.env.MCP_API_URL || 'http://mcp.local/wp-json/wp/v2/wpmcp/streamable';
  const bearerToken = process.env.MCP_JWT_TOKEN;

  if (!bearerToken) {
    return NextResponse.json({ error: 'MCP JWT token is not configured.' }, { status: 500 });
  }

  // Define the request body for fetching the AI prompt
  const promptRequestBody = {
    jsonrpc: '2.0',
    method: 'prompts/get',
    params: {
      name: 'analyze-logs',
    },
    id: 1,
  };

  // Define the request body for fetching the logs via the tool
  const toolRequestBody = {
    jsonrpc: "2.0",
    method: "tools/call",
    params: {
        name: "wpgraphql_logging_custom_tool",
        arguments: {
            "timeframe_hours": 24,
            "level": "ERROR"
        }
    },
    id: 2
  };

  try {
    // Step 1: Fetch the system prompt from WordPress
    const promptResponse = await fetch(wordpressUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${bearerToken}`,
        'Accept': 'application/json, text/event-stream',
      },
      body: JSON.stringify(promptRequestBody),
    });

    if (!promptResponse.ok) {
      const errorText = await promptResponse.text();
      console.error('WordPress API Error (prompts/get):', errorText);
      return NextResponse.json({ error: `Failed to fetch prompt from WordPress: ${promptResponse.statusText}` }, { status: promptResponse.status });
    }

    const promptData = await promptResponse.json();
    const systemPrompt = promptData.result?.messages?.[0]?.content?.text;

    if (!systemPrompt) {
      console.error('Unexpected prompt response structure:', JSON.stringify(promptData, null, 2));
      return NextResponse.json({ error: 'Could not extract system prompt from WordPress response.' }, { status: 500 });
    }

    // Step 2: Fetch the logs using the tool from WordPress
    const toolResponse = await fetch(wordpressUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${bearerToken}`,
        'Accept': 'application/json, text/event-stream',
      },
      body: JSON.stringify(toolRequestBody),
    });

    if (!toolResponse.ok) {
      const errorText = await toolResponse.text();
      console.error('WordPress API Error (tools/call):', errorText);
      return NextResponse.json({ error: `Failed to fetch logs from WordPress: ${toolResponse.statusText}` }, { status: toolResponse.status });
    }

    const toolData = await toolResponse.json();
    const logsToAnalyze = toolData.result?.content?.[0]?.text;

    if (!logsToAnalyze) {
        console.error('Unexpected tool response structure:', JSON.stringify(toolData, null, 2));
        return NextResponse.json({ error: 'Could not extract logs from WordPress tool response.' }, { status: 500 });
    }

    // Pretty-print the logs for display on the frontend
    let prettyLogs;
    try {
        prettyLogs = JSON.stringify(JSON.parse(logsToAnalyze), null, 2);
    } catch {
        prettyLogs = logsToAnalyze; // If it's not a JSON string, display as is
    }

    // Step 3: Send logs and prompt to Google Gemini for analysis
    const fullPrompt = `${systemPrompt}\n\nPlease format your response using Markdown for clear readability. Use headings, lists, and code blocks where appropriate.\n\nHere are the logs to analyze:\n\n${logsToAnalyze}`;

    const result = await model.generateContent(fullPrompt);
    const response = await result.response;
    const analysis = response.text();

    return NextResponse.json({ logs: prettyLogs, analysis: analysis });

  } catch (error) {
    console.error('Error in analyze-logs API route:', error);
    return NextResponse.json({ error: error.message || 'An internal server error occurred.' }, { status: 500 });
  }
}
