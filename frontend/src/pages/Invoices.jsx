import { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import {isCustomer, isSystemUser} from "../utils/auth.jsx";

export default function Invoices({ userType = "admin" }) {
    const [invoices, setInvoices] = useState([]);
    const [loading, setLoading] = useState(false);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [filters, setFilters] = useState({
        customer: "",
        dateFrom: "",
        dateTo: "",
    });

    useEffect(() => {
        fetchInvoices();
    }, [page, filters]);

    const fetchInvoices = async () => {
        setLoading(true);
        try {
            const res = await axios.get("/invoices", {
                params: {
                    page,
                    customer: filters.customer || undefined,
                    date_from: filters.dateFrom || undefined,
                    date_to: filters.dateTo || undefined,
                },
            });
            console.log('res.data.data', res.data.data)
            setInvoices(res.data.data);
            setLastPage(res.data.last_page);
        } catch (err) {
            console.error(err);
            alert("Failed to load invoices");
        } finally {
            setLoading(false);
        }
    };

    const handleFilterChange = (key, value) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
        setPage(1);
    };

    return (
        <div className="p-4">
            <h2 className="text-lg font-semibold mb-4">
                {isCustomer() ? "My Invoices" : "All Invoices"}
            </h2>

            {/* Filters */}
            <div className="flex flex-wrap gap-3 mb-4">
                {isSystemUser() && (
                    <input
                        type="text"
                        placeholder="Search customer name/code"
                        className="border p-2"
                        value={filters.customer}
                        onChange={(e) => handleFilterChange("customer", e.target.value)}
                    />
                )}

                <input
                    type="date"
                    className="border p-2"
                    value={filters.dateFrom}
                    onChange={(e) => handleFilterChange("dateFrom", e.target.value)}
                />

                <input
                    type="date"
                    className="border p-2"
                    value={filters.dateTo}
                    onChange={(e) => handleFilterChange("dateTo", e.target.value)}
                />
            </div>

            {/* Table */}
            {loading ? (
                <p>Loading...</p>
            ) : (
                <table className="w-full border text-sm">
                    <thead>
                    <tr className="bg-gray-100 text-left">
                        <th className="border p-2">Invoice #</th>
                        {isSystemUser() && <th className="border p-2">Customer</th>}
                        <th className="border p-2 text-right">Amount (USD)</th>
                        <th className="border p-2 text-center">Created</th>
                        <th className="border p-2 text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    {invoices.map((inv) => (
                        <tr key={inv.id}>
                            <td className="border p-2">#{inv.id}</td>
                            {isSystemUser() && (
                                <td className="border p-2">
                                    {inv.customer?.name || "—"} ({inv.customer?.customer_code})
                                </td>
                            )}
                            <td className="border p-2 text-right">
                                {parseFloat(inv.total_amount_usd)?.toFixed(2)}
                            </td>
                            <td className="border p-2 text-center">
                                {new Intl.DateTimeFormat("en-GB", {
                                    dateStyle: "medium",
                                    timeStyle: "short",
                                }).format(new Date(inv.created_at))}
                            </td>
                            <td className="border p-2 text-center">
                                <Link
                                    to={`/invoices/${inv.id}`}
                                    className="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            )}

            {/* Pagination */}
            <div className="flex justify-center gap-2 mt-4">
                <button
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page === 1}
                    className="px-3 py-1 border rounded disabled:opacity-50"
                >
                    Prev
                </button>
                <span>
                    Page {page} / {lastPage}
                </span>
                <button
                    onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
                    disabled={page === lastPage}
                    className="px-3 py-1 border rounded disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    );
}
