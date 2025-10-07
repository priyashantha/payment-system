import { Link, useNavigate } from "react-router-dom";
import { isAuthenticated, logout } from "../utils/auth";

export default function Header() {
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate("/login");
    };

    return (
        <header className="bg-gray-900 text-white p-4 flex justify-between items-center">
            <h1 className="font-semibold text-lg">Payment Notification & Payout System</h1>
            <nav className="space-x-4">
                <Link to="/">Home</Link>
                {isAuthenticated() && (
                    <>
                        <Link to="/uploads">Payments</Link>
                        <Link to="/invoices">Invoices</Link>
                        <button
                            onClick={handleLogout}
                            className="bg-red-500 px-3 py-1 rounded text-sm"
                        >
                            Logout
                        </button>
                    </>
                )}
            </nav>
        </header>
    );
}
