import Products from "@/components/Products/Products";

export default function Shop() {
  return (
    <>    
      <Products />
    </>
  );
}

Shop.queries = [
  Products.query,
];
