import { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import {isCustomer} from "../utils/auth.jsx";
import {formatNumber} from "../utils/helpers.jsx";

export default function InvoiceDetail() {
    const { id } = useParams();
    const [invoice, setInvoice] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        fetchInvoice();
    }, [id]);

    const fetchInvoice = async () => {
        const endPoint = isCustomer() ? '/customer-invoices' : '/invoices';
        setLoading(true);
        try {
            const res = await axios.get(`${endPoint}/${id}`);
            setInvoice(res.data);
        } catch (err) {
            console.error(err);
            alert("Failed to load invoice details");
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <p className="p-6">Loading...</p>;
    if (!invoice) return <p className="p-6">Invoice not found.</p>;

    const customer = invoice.customer || {};

    return (
        <div className="flex flex-col items-center min-h-scree py-10 px-4">
            <div className="bg-white shadow-md rounded-lg p-8 max-w-3xl w-full border border-gray-200">
                <h2 className="text-xl font-semibold mb-4 text-gray-800">
                    Daily Invoice Summary
                </h2>

                <div className="mb-6 text-sm text-gray-700 space-y-1">
                    <p>
                        <strong>Date:</strong>{" "}
                        {new Intl.DateTimeFormat("en-US", {
                            dateStyle: "long",
                        }).format(new Date(invoice.created_at))}
                    </p>
                    <p>
                        <strong>Customer ID:</strong> {customer.customer_code || "—"}
                    </p>
                    <p>
                        <strong>Email:</strong> {customer.email || "—"}
                    </p>
                </div>

                {/* Payments table */}
                <div className="overflow-x-auto">
                    <table className="w-full border border-gray-300 text-sm">
                        <thead className="bg-gray-100">
                        <tr>
                            <th className="border p-2 text-left">Date</th>
                            <th className="border p-2 text-left">Reference</th>
                            <th className="border p-2 text-right">Amount (Original)</th>
                            <th className="border p-2 text-left">Currency</th>
                            <th className="border p-2 text-right">Amount (USD)</th>
                        </tr>
                        </thead>
                        <tbody>
                        {invoice.payments?.map((p) => (
                            <tr key={p.id}>
                                <td className="border p-2 text-gray-700">
                                    {new Intl.DateTimeFormat("en-GB", {
                                        dateStyle: "short",
                                        timeStyle: "short",
                                    }).format(new Date(p.date_time))}
                                </td>
                                <td className="border p-2 text-gray-700">
                                    {p.reference_no}
                                </td>
                                <td className="border p-2 text-right text-gray-700">
                                    {formatNumber(p.amount_original)}
                                </td>
                                <td className="border p-2 text-gray-700">{p.currency}</td>
                                <td className="border p-2 text-right text-gray-700">
                                    {formatNumber(p.amount_usd)}
                                </td>
                            </tr>
                        ))}
                        <tr className="bg-gray-50 font-semibold">
                            <td colSpan="4" className="border p-2 text-right">
                                Total (USD)
                            </td>
                            <td className="border p-2 text-right">
                                {formatNumber(invoice.total_amount_usd)}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <p className="mt-6 text-gray-600 text-sm">
                    Thank you for your continued business.
                </p>

                <div className="mt-6 text-right">
                    <Link
                        to="/invoices"
                        className="text-sm text-blue-600 hover:underline"
                    >
                        ← Back to Invoices
                    </Link>
                </div>
            </div>
        </div>
    );
}
