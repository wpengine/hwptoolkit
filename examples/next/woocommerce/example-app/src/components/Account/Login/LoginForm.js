import React, { useState } from "react";
import { useAuth } from "@/lib/auth/AuthProvider";
//import { useSession } from "@/lib/woocommerce/sessionProvider";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

const LoginForm = () => {
  const {login} = useAuth();
  //const { login } = useSession();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    event.preventDefault();
    login(username, password);
  };

  return (
    <div className="max-w-md mx-auto p-4">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="username" className="block mb-1">
            Username
          </label>
          <input
            id="username"
            type="text"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            className="w-full p-2 border"
            required
          />
        </div>

        <div>
          <label htmlFor="password" className="block mb-1">
            Password
          </label>
          <input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="w-full p-2 border"
            required
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          className={`w-full p-2 rounded ${
            loading ? "bg-gray-300" : "bg-blue-600 text-white hover:bg-blue-700"
          }`}
        >
          {loading ? "Logging in..." : "Login"}
        </button>

        {error && (
          <Alert variant="destructive">
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}
      </form>
    </div>
  );
};

LoginForm.displayName = "LoginForm";
export default LoginForm;
