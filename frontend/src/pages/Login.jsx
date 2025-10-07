import { useState } from "react";
import axios from "axios";
import { login } from "../utils/auth";
import { useNavigate } from "react-router-dom";

export default function Login() {
    const [mode, setMode] = useState("admin"); // "admin" or "customer"
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const endpoint =
                mode === "admin" ? "/admin/login" : "/customer/login";
            const res = await axios.post(endpoint, { email, password });

            // res.data should return { token, user }
            login(res.data.token, res.data.user, mode === "admin" ? "user" : "customer");

            navigate("/");
        } catch (err) {
            alert("Invalid credentials or server error");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-sm mx-auto mt-10 bg-white shadow p-6 rounded">
            <h2 className="text-xl font-semibold mb-4 text-center">
                {mode === "admin" ? "Admin / Customer Login" : "Customer Login"}
            </h2>

            <div className="flex mb-4 border-b">
                <button
                    onClick={() => setMode("admin")}
                    className={`flex-1 py-2 ${
                        mode === "admin"
                            ? "border-b-2 border-blue-600 font-semibold"
                            : "text-gray-500"
                    }`}
                >
                    Admin
                </button>
                <button
                    onClick={() => setMode("customer")}
                    className={`flex-1 py-2 ${
                        mode === "customer"
                            ? "border-b-2 border-blue-600 font-semibold"
                            : "text-gray-500"
                    }`}
                >
                    Customer
                </button>
            </div>

            <form onSubmit={handleSubmit}>
                <input
                    type="email"
                    placeholder="Email"
                    className="border p-2 w-full mb-3 rounded"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <input
                    type="password"
                    placeholder="Password"
                    className="border p-2 w-full mb-4 rounded"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                />
                <button
                    disabled={loading}
                    className="bg-blue-600 text-white w-full p-2 rounded hover:bg-blue-700"
                >
                    {loading ? "Logging in..." : "Login"}
                </button>
            </form>
        </div>
    );
}
