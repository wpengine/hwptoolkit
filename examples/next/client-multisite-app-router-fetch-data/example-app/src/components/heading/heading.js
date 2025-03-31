import { capitalizeWords } from "@/lib/utils";

export function Heading({
  heading,
  className = "text-3xl lg:text-4xl font-bold mb-8 container max-w-4xl text-center lg:text-left lg:px-10 py-2 mx-auto"
}) {
  const capitalizeHeading = capitalizeWords(heading);

  return (
    <h1 className={className}>
      {capitalizeHeading ? `${capitalizeHeading}` : heading}
    </h1>
  );
};
