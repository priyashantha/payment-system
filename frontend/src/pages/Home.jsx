import { Link } from "react-router-dom";
import { isAuthenticated, isSystemUser, isCustomer, getUser } from "../utils/auth";

export default function Home() {
    const loggedIn = isAuthenticated();
    const user = getUser();
    const admin = isSystemUser();
    const customer = isCustomer();

    return (
        <div className="flex flex-col items-center justify-center mt-16 text-center">
            <h2 className="text-3xl font-bold mb-3">Welcome to the Payment System</h2>
            <p className="text-gray-600 mb-8 max-w-md">
                Securely upload payment batches, review processing results, and view invoices.
            </p>

            {!loggedIn && (
                <>
                    <p className="text-gray-700 mb-4">Please log in to access your dashboard.</p>
                    <Link
                        to="/login"
                        className="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition"
                    >
                        Login
                    </Link>
                </>
            )}

            {loggedIn && (
                <div className="bg-gray-100 p-6 rounded-lg shadow-md w-full max-w-md">
                    <p className="text-gray-800 mb-4">
                        You are logged in as{" "}
                        <span className="font-semibold">{user?.name}</span>{" "}
                        <span className="text-sm text-gray-500">
              ({admin ? "Admin / Finance User" : "Customer"})
            </span>
                    </p>

                    {admin && (
                        <div className="flex justify-center gap-3">
                            <Link
                                to="/uploads"
                                className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                            >
                                Manage Payments
                            </Link>
                            <Link
                                to="/invoices"
                                className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                            >
                                View All Invoices
                            </Link>
                        </div>
                    )}

                    {customer && (
                        <div className="flex justify-center">
                            <Link
                                to="/invoices"
                                className="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700"
                            >
                                View My Invoices
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
