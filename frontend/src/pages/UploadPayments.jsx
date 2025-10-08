import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import axios from "axios";

export default function UploadPayments() {
    const { id } = useParams();
    const [records, setRecords] = useState([]);
    const [loading, setLoading] = useState(false);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [filters, setFilters] = useState({ status: "", customer: "" });

    useEffect(() => {
        fetchPayments();
    }, [id, page, filters]);

    const handleFilterChange = (key, value) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
        setPage(1);
    };

    const fetchPayments = async () => {
        setLoading(true);
        try {
            const res = await axios.get(`/payment-uploads/${id}`, {
                params: {
                    page,
                    status: filters.status || undefined,
                    customer: filters.customer || undefined,
                },
            });

            // ✅ new structure
            setRecords(res.data.payments.data);
            setLastPage(res.data.payments.last_page);

        } catch (err) {
            console.error(err);
            alert("Failed to load payments");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-4">
            <h2 className="text-lg font-semibold mb-4">
                Payments for Upload #{id}
            </h2>

            {/* Filters */}
            <div className="flex gap-3 mb-4">
                <select
                    className="border p-2"
                    value={filters.status}
                    onChange={(e) =>
                        handleFilterChange("status", e.target.value)
                    }
                >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processed">Processed</option>
                </select>

                <input
                    type="text"
                    placeholder="Search customer name/code"
                    className="border p-2"
                    value={filters.customer}
                    onChange={(e) =>
                        handleFilterChange("customer", e.target.value)
                    }
                />

                <button
                    onClick={() => setPage(1)}
                    className="bg-gray-200 px-3 rounded"
                >
                    Filter
                </button>
            </div>

            {/* Table */}
            {loading ? (
                <p>Loading...</p>
            ) : (
                <table className="w-full border text-sm">
                    <thead>
                    <tr className="bg-gray-100">
                        <th className="border p-2">Ref</th>
                        <th className="border p-2">Customer</th>
                        <th className="border p-2">Amount</th>
                        <th className="border p-2">Currency</th>
                        <th className="border p-2">Status</th>
                        <th className="border p-2">Created</th>
                    </tr>
                    </thead>
                    <tbody>
                    {records.map((r) => (
                        <tr key={r.id}>
                            <td className="border p-2">{r.reference_no}</td>
                            <td className="border p-2">
                                {r.customer?.name || "—"} ({r.customer?.customer_code})
                            </td>
                            <td className="border p-2 text-right">{r.amount_original}</td>
                            <td className="border p-2">{r.currency}</td>
                            <td className="border p-2">
                                <span
                                    className={`font-medium ${
                                        r.status === "failed"
                                            ? "text-red-600"
                                            : r.status === "processed"
                                            ? "text-green-600"
                                            : "text-gray-600"
                                    }`}
                                >
                                    {r.status}
                                </span>
                                {r.status === "failed" && (
                                    <div className="text-xs text-red-500 mt-1">{r.error_message}</div>
                                )}

                                {r.status === "pending" && (
                                    <div className="text-xs text-gray-400 mt-1 italic">
                                        Awaiting processing
                                    </div>
                                )}
                            </td>
                            <td className="border p-2">
                                {new Intl.DateTimeFormat("en-GB", {
                                    dateStyle: "medium",
                                    timeStyle: "short",
                                }).format(new Date(r.created_at))}
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
