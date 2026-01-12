import type { Metadata } from 'next';
import { Toolbar } from './components/Toolbar';
import './globals.css';
import '@wpengine/hwp-toolbar/styles';

export const metadata: Metadata = {
  title: 'Toolbar Demo - React Hooks',
  description: 'Headless WordPress Toolbar with React hooks',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body>
        {children}
        <Toolbar />
      </body>
    </html>
  );
}
