import Products from "@/components/Products/Products";

export default function Shop() {
  return (
    <>    
      <Products loadMore={true} />
    </>
  );
}

Shop.queries = [
  Products.query,
];
