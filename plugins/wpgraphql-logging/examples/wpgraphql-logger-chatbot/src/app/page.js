'use client';

import { useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

export default function Home() {
  const [logs, setLogs] = useState('');
  const [analysis, setAnalysis] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleAnalyzeLogs = async () => {
    setLoading(true);
    setError('');
    setLogs('');
    setAnalysis('');

    try {
      const response = await fetch('/api/analyze-logs', {
        method: 'POST',
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Something went wrong');
      }

      const data = await response.json();
      setLogs(data.logs);
      setAnalysis(data.analysis);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-gray-900 min-h-screen text-white font-sans">
      <div className="container mx-auto p-4 sm:p-6 lg:p-8">
        <header className="text-center mb-8">
          <h1 className="text-4xl sm:text-5xl font-bold text-cyan-400 tracking-tight">
            WPGraphQL Log Analyzer
          </h1>
          <p className="text-gray-400 mt-2 text-lg">
            Get AI-powered analysis of your WordPress GraphQL logs.
          </p>
        </header>

        <main>
          <div className="flex justify-center mb-8">
            <button
              onClick={handleAnalyzeLogs}
              disabled={loading}
              className="bg-cyan-500 hover:bg-cyan-600 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-lg shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out"
            >
              {loading ? 'Analyzing...' : 'Analyze Logs for Last 24 Hours'}
            </button>
          </div>

          {error && (
            <div className="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-lg relative mb-6" role="alert">
              <strong className="font-bold">Error: </strong>
              <span className="block sm:inline">{error}</span>
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="bg-gray-800 p-6 rounded-lg shadow-xl">
              <h2 className="text-2xl font-semibold mb-4 text-cyan-300 border-b-2 border-cyan-800 pb-2">Raw Logs</h2>
              <div className="bg-gray-900 p-4 rounded-md h-96 overflow-y-auto text-sm font-mono whitespace-pre-wrap">
                {loading && !logs && <p className="text-gray-400">Loading logs...</p>}
                {logs ? logs.replace(/\\n/g, '\n') : <p className="text-gray-500">Logs will appear here...</p>}
              </div>
            </div>
            <div className="bg-gray-800 p-6 rounded-lg shadow-xl">
              <h2 className="text-2xl font-semibold mb-4 text-cyan-300 border-b-2 border-cyan-800 pb-2">AI Analysis</h2>
              <div className="bg-gray-900 p-4 rounded-md h-96 overflow-y-auto prose prose-invert prose-sm max-w-none prose-li:mb-3 prose-p:mb-3">
                 {loading && !analysis && <p className="text-gray-400">AI is analyzing the logs...</p>}
                 {analysis ? (
                    <ReactMarkdown
                      remarkPlugins={[remarkGfm]}
                    >
                      {analysis}
                    </ReactMarkdown>
                  ) : (
                    <p className="text-gray-500">Analysis will appear here...</p>
                  )}
              </div>
            </div>
          </div>
        </main>
        <footer className="text-center mt-12 text-gray-500 text-sm">
            <p>Powered by Next.js and Google Gemini</p>
						<p>(model: gemini-1.5-flash)</p>
        </footer>
      </div>
    </div>
  );
}
