import Link from "next/link";

export function LoadMoreButton({
  text = "Load More Posts",
  loadingText = "Loading...",
  onClick,
  loading = false,
  className = "text-center p-3 bg-orange-600 hover:bg-orange-400 text-white uppercase transition-colors duration-300",
}) {
  return (
    <div className="flex flex-col items-center my-4">
      <button
        onClick={onClick}
        type="button"
        className={className}
        disabled={loading}
      >
        {loading ? loadingText : text}
      </button>
    </div>
  );
}
