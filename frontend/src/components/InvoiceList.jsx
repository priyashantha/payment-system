import { useEffect, useState } from "react";
import axios from "axios";

export default function InvoiceList() {
    const [invoices, setInvoices] = useState([]);

    useEffect(() => {
        axios.get("/api/invoices").then((res) => setInvoices(res.data));
    }, []);

    return (
        <div className="mt-6">
            <h3 className="font-semibold mb-2">Invoices</h3>
            <ul className="list-disc ml-5">
                {invoices.map((i) => (
                    <li key={i.id}>
                        <a
                            href={i.download_url}
                            target="_blank"
                            rel="noreferrer"
                            className="text-blue-600 underline"
                        >
                            {i.filename}
                        </a>
                    </li>
                ))}
            </ul>
        </div>
    );
}
