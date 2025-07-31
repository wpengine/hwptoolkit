import React from "react";

const RouteDataContext = React.createContext();

export const useRouteData = () => {
  const context = React.useContext(RouteDataContext);
  if (!context) {
    throw new Error(
      "useRouteData must be used within a RouteDataContext.Provider"
    );
  }
  return context;
};
export const RouteDataProvider = ({ children, value }) => {
  return (
    <RouteDataContext.Provider value={value}>
      {children}
    </RouteDataContext.Provider>
  );
};
