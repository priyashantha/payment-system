import React, { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import Pagination from "../components/Pagination";

export default function UploadedList() {
    const [records, setRecords] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    useEffect(() => {
        axios.get(`/payment-uploads?page=${page}`).then((res) => {
            setRecords(res.data.data || res.data);
            if (res.data.last_page) setLastPage(res.data.last_page);
        });
    }, [page]);

    return (
        <div className="mt-6">
            <h3 className="font-semibold mb-2">Payment File Uploads</h3>
            <table className="w-full border text-sm">
                <thead>
                <tr className="bg-gray-200 text-left">
                    <th className="border p-2">ID</th>
                    <th className="border p-2">Filename</th>
                    <th className="border p-2">Status</th>
                    <th className="border p-2">Uploaded At</th>
                    <th className="border p-2">Action</th>
                </tr>
                </thead>
                <tbody>
                {records.map((r) => (
                    <tr key={r.id}>
                        <td className="border p-2">{r.id}</td>
                        <td className="border p-2">{r.filename}</td>
                        <td className="border p-2">{r.status}</td>
                        <td className="border p-2">
                            {new Date(r.created_at).toLocaleString()}
                        </td>
                        <td className="border p-2 text-center">
                            <Link
                                to={`/uploads/${r.id}`}
                                className="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1 rounded"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                ))}
                </tbody>
            </table>

            {/* ✅ Reusable Pagination */}
            <Pagination page={page} lastPage={lastPage} setPage={setPage} />
        </div>
    );
}
