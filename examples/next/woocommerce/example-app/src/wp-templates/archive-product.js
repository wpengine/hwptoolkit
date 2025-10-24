import RecentProducts from "@/components/Products/Products";

export default function Shop() {
  return (
    <>
      <RecentProducts />
    </>
  );
}

Shop.queries = [
  RecentProducts.query, // Ensure RecentProducts query is included
];
