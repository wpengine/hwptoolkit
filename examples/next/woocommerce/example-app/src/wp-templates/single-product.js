export default function SingleProduct({ graphqlData }) {
  const { SingleProductQuery } = graphqlData;
  console.log('SingleProductQuery:', SingleProductQuery);
  

  if (!SingleProductQuery || !SingleProductQuery.product) {
    return <div>Product not found</div>;
  }
  
  return (
    <>
      <div id="single-template">

        <h2>{SingleProductQuery.product.name}</h2>
        <div
          dangerouslySetInnerHTML={{
            __html: SingleProductQuery.product.description,
          }}
        />
        

        <div className="product-details">
          <p><strong>Price:</strong> ${SingleProductQuery.product.price}</p>
          <p><strong>SKU:</strong> {SingleProductQuery.product.sku}</p>
          <p><strong>Stock Status:</strong> {SingleProductQuery.product.stockStatus}</p>
          <p><strong>Date:</strong> {SingleProductQuery.product.date}</p>
        </div>
      </div>
    </>
  );
}

SingleProduct.queries = [
  {
    name: "SingleProductQuery", 
    query: /* GraphQL */ `
      query SingleProductQuery($id: ID!) {
        product(id: $id, idType: SLUG) {
          id
          databaseId
          name
          slug
          uri
          description
          shortDescription
          sku
          date
          onSale
          image {
            id
            sourceUrl
            altText
            mediaDetails {
              width
              height
            }
          }
          galleryImages {
            nodes {
              id
              sourceUrl
              altText
              mediaDetails {
                width
                height
              }
            }
          }
          

          ... on SimpleProduct {
            price
            regularPrice
            salePrice
            stockStatus
            stockQuantity
            weight
            length
            width
            height
          }
          
          ... on VariableProduct {
            price
            regularPrice
            salePrice
            stockStatus
            stockQuantity
            weight
            length
            width
            height
            variations {
              nodes {
                id
                name
                price
                regularPrice
                salePrice
                stockStatus
                stockQuantity
              }
            }
          }
          
          ... on ExternalProduct {
            price
            regularPrice
            salePrice
            externalUrl
            buttonText
          }
          
          ... on GroupProduct {
            price
            regularPrice
            salePrice
            products {
              nodes {
                id
                name
                slug
              }
            }
          }
        }
      }
    `,
    variables: (event, { uri }) => {
      const slug = uri.replace(/^\/+|\/+$/g, '').split('/').pop();
      return {
        id: slug,
      };
    },
  },
];