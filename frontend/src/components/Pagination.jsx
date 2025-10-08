import React, { useState } from "react";

export default function Pagination({ page, lastPage, setPage }) {
    const [inputPage, setInputPage] = useState(page);

    // Keep local input value in sync when page changes externally
    React.useEffect(() => {
        setInputPage(page);
    }, [page]);

    const handlePageChange = (e) => {
        e.preventDefault();
        const num = Number(inputPage);
        if (!isNaN(num) && num >= 1 && num <= lastPage) {
            setPage(num);
        } else {
            setInputPage(page); // reset to valid value if invalid
        }
    };

    if (lastPage <= 1) return null;

    return (
        <div className="flex justify-center items-center gap-2 mt-4">
            <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
                className="px-3 py-1 border rounded disabled:opacity-50"
            >
                Prev
            </button>

            <form onSubmit={handlePageChange} className="flex items-center gap-1">
                <input
                    type="number"
                    min="1"
                    max={lastPage}
                    value={inputPage}
                    onChange={(e) => setInputPage(e.target.value)}
                    className="w-16 text-center border rounded px-1 py-0.5"
                />
                <span>/ {lastPage}</span>
            </form>

            <button
                onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
                disabled={page === lastPage}
                className="px-3 py-1 border rounded disabled:opacity-50"
            >
                Next
            </button>
        </div>
    );
}
