import Link from "next/link";

export function Button({
  text,
  href,
  className = "text-center p-3 bg-orange-600 hover:bg-orange-400 text-white uppercase transition-colors duration-300",
}) {
  return (
    <Link href={href} className={className}>
      {text}
    </Link>
  );
}
