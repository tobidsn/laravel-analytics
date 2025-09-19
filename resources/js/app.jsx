import React from 'react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
import GoogleAnalytics from './Components/Analytics/GoogleAnalytics';

// Initialize the Analytics Dashboard
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('analytics-dashboard');
  if (container) {
    const root = createRoot(container);
    root.render(<GoogleAnalytics />);
  }
});