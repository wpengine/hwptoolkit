import React, { useState, useEffect } from "react";

interface LoadingProps {
  showSpinner?: boolean;
  text?: string;
}

const LoadingSpinner: React.FC<LoadingProps> = ({ showSpinner = true, text = "Loading..." }) => {
  return (
    <div className="loading-container">
      {showSpinner && <div className="spinner"></div>}
      <p className="loading-text">{text}</p>
    </div>
  );
};

export default LoadingSpinner;
