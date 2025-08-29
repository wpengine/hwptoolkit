import { createContext, useContext, useEffect, useReducer } from 'react';
import { useQuery, useMutation } from '@apollo/client';
import { GetCart, Login, UpdateCustomer } from '@/lib/woocommerce/graphQL';

const initialSession = {
  cart: null,
  customer: null,
};

export const SessionContext = createContext(initialSession);

const reducer = (state, action) => {
  switch (action.type) {
    case 'SET_CART':
      return {
        ...state,
        cart: action.payload,
      };
    case 'SET_CUSTOMER':
      return {
        ...state,
        customer: action.payload,
      };
    default:
      throw new Error('Invalid action dispatched to session reducer');
  }
};

const { Provider } = SessionContext;

export function SessionProvider({ children }) {
  const [state, dispatch] = useReducer(reducer, initialSession);

  const { data, loading: fetching } = useQuery(GetCart);
  const [executeLogin, { data: loginData, errors: loginErrors }] = useMutation(Login);
  const [executeUpdateCustomer, { data: updateCustomerData, errors: updateCustomerErrors }] = useMutation(UpdateCustomer);

  useEffect(() => {
    if (data?.cart) {
      dispatch({
        type: 'SET_CART',
        payload: data.cart,
      });
    }

    if (data?.customer) {
      dispatch({
        type: 'SET_CUSTOMER',
        payload: data.customer,
      });
    }
  }, [data]);

  const setCart = (cart) => dispatch({
    type: 'SET_CART',
    payload: cart,
  });

  const setCustomer = (customer) => dispatch({
    type: 'SET_CUSTOMER',
    payload: customer,
  });

  useEffect(() => {
    if (loginData?.login) {
      const { 
        authToken,
        refreshToken,
        customer
      } = loginData.login;

      sessionStorage.getItem(process.env.AUTH_TOKEN_SS_KEY, authToken);
      localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY, refreshToken);

      setCustomer(customer);
    }
  }, [loginData]);

  useEffect(() => {
    if (updateCustomerData?.updateCustomer) {
      const { customer } = updateCustomerData.updateCustomer;

      setCustomer(customer);
    }
  }, [updateCustomerData]);

  const login = (username, password) => {
    return executeLogin({ username, password });
  }

  const updateCustomer = (input) => {
    return executeUpdateCustomer({ input });
  }

  const store = {
    ...state,
    fetching,
    setCart,
    setCustomer,
    login,
    updateCustomer,
  };
  return (
    <Provider value={store}>{children}</Provider>
  );
}

export const useSession = () => useContext(SessionContext);
