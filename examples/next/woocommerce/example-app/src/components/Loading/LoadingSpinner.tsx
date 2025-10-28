import React from "react";

interface LoadingProps {
	text?: string;
}

const LoadingSpinner: React.FC<LoadingProps> = ({ text }) => {
	return (
		<div className="min-h-screen flex flex-col items-center justify-center">
			<div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
			{text && <p className="loading-text mt-4">{text}</p>}
		</div>
	);
};

export default LoadingSpinner;
