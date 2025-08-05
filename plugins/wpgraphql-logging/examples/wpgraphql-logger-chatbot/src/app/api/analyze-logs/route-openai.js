import { NextResponse } from 'next/server';
import OpenAI from 'openai';

export async function POST() {
  const openai = new OpenAI({
    apiKey: process.env.OPENAI_API_KEY,
  });

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

    // Step 3: Send logs and prompt to OpenAI for analysis
    const completion = await openai.chat.completions.create({
      model: 'gpt-3.5-turbo',
      messages: [
        { role: 'system', content: systemPrompt },
        { role: 'user', content: `Here are the logs to analyze:\n\n${logsToAnalyze}` },
      ],
    });

    const analysis = completion.choices[0].message.content;

    return NextResponse.json({ logs: prettyLogs, analysis: analysis });

  } catch (error) {
    console.error('Error in analyze-logs API route:', error);
    return NextResponse.json({ error: error.message || 'An internal server error occurred.' }, { status: 500 });
  }
}
