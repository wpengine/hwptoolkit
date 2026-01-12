import { gql } from "@apollo/client";
import { OrderFields } from "./cartGraphQL";

export const CHECKOUT_MUTATION = gql`
	mutation checkout($input: CheckoutInput!) {
		checkout(input: $input) {
			order {
				...OrderFields
				orderKey
			}
			redirect
			result
			clientMutationId
		}
	}
	${OrderFields}
`;
