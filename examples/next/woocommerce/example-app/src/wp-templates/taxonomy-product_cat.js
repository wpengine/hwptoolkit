import RecentProducts from "@/components/Products/Products";

export default function TaxonomyProductCat() {
  return (
    <>
      <RecentProducts />
    </>
  );
}

TaxonomyProductCat.queries = [
  RecentProducts.query, // Ensure RecentProducts query is included
];
