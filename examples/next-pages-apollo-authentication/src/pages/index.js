import Link from "next/link";
import { useAuth } from "@/lib/auth/AuthProvider";
import { gql, useQuery } from "@apollo/client";

const GET_USER_SETTINGS = gql`
  query GetUserSettings {
    viewer {
      email
      username
    }
  }
`;

export default function Home() {
  const { tokens, isLoading, logout, refreshAuth } = useAuth();
  const isAuthenticated = !!tokens?.authToken;

  const { data, loading: userDataLoading } = useQuery(GET_USER_SETTINGS, {
    skip: !isAuthenticated,
  });

  return (
    <div className="min-h-screen p-8">
      <header className="flex justify-between items-center mb-12">
        <h1 className="text-2xl font-bold">My WordPress App</h1>
        <div>
          {isLoading ? (
            <p>Loading...</p>
          ) : isAuthenticated ? (
            <button onClick={logout} className='bg-blue-600 text-white px-4 py-2 rounded-md leading-[normal]'>
              Sign out
            </button>
          ) : (
            <Link
              href="/login"
              className="bg-blue-600 text-white px-4 py-2 rounded-md"
            >
              Login
            </Link>
          )}
        </div>
      </header>

      <main>
        <section className="mb-8">
          <h2 className="text-xl font-semibold mb-4">Welcome to the App</h2>
          <p>
            This is a simple application connected to WordPress via GraphQL.
          </p>
        </section>

        {isAuthenticated && (
          <section className="bg-gray-100 p-6 rounded-lg mb-8">
            <h2 className="text-xl font-semibold mb-4">Your Account</h2>
            {userDataLoading ? (
              <p>Loading your information...</p>
            ) : data?.viewer ? (
              <div className="space-y-2">
                <p>
                  <strong>Email:</strong> {data.viewer.email}
                </p>
                <p>
                  <strong>Username:</strong> {data.viewer.username}
                </p>
              </div>
            ) : (
              <p>Could not load user data</p>
            )}
          </section>
        )}

        {isAuthenticated && (
          <section className="bg-gray-100 p-6 rounded-lg mb-8">
            <h2 className="text-xl font-semibold mb-4">
              Authentication Tokens
            </h2>
            {tokens ? (
              <div className="space-y-2">
                <p>
                  <strong>Auth Token:</strong> {tokens.authToken}
                </p>
                <p>
                  <strong>Refresh Token:</strong>{" "}
                  {tokens.refreshToken}
                </p>
                <p>
                  <strong>Auth Token Expiration:</strong>{" "}
                  {tokens.authTokenExpiration}
                </p>
                <p>
                  <strong>Refresh Token Expiration:</strong>{" "}
                  {tokens.refreshTokenExpiration}
                </p>
              </div>
            ) : (
              <p>No tokens available</p>
            )}
            <button
              onClick={async () => await refreshAuth()}
              className="text-blue-600 hover:underline"
            >
              Refresh Tokens
            </button>
          </section>
        )}

        <section>
          <h2 className="text-xl font-semibold mb-4">Getting Started</h2>
          <ul className="list-disc ml-5 space-y-2">
            <li>
              {isAuthenticated ? (
                <div>
                  <button
                    onClick={logout}
                    className="text-blue-600 hover:underline mr-2"
                  >
                    Sign out
                  </button>
                </div>
              ) : (
                <Link href="/login" className="text-blue-600 hover:underline">
                  Sign in
                </Link>
              )}{" "}
              {isAuthenticated ? "to sign-out of" : "to access"} your
              WordPress account
            </li>
          </ul>
        </section>
      </main>
    </div>
  );
}
