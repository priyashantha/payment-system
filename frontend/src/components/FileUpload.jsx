import { useState, useRef } from "react";
import axios from "axios";

export default function FileUpload({ onUploadSuccess }) {
    const [file, setFile] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const fileInputRef = useRef(null);

    const upload = async () => {
        if (!file) return alert("Please select a file");

        const formData = new FormData();
        formData.append("file", file);

        try {
            setIsUploading(true);
            await axios.post("/payment-uploads", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            alert("File uploaded successfully and queued for processing.");
            if (onUploadSuccess) onUploadSuccess();

            setFile(null);
            if (fileInputRef.current) fileInputRef.current.value = "";
        } catch (error) {
            if (error.response?.status === 413) {
                alert(error.response.data.message || "File is too large (limit 200MB).");
            } else {
                alert("Upload failed. Please try again.");
            }
        } finally {
            setIsUploading(false);
        }
    };

    return (
        <div className="bg-white p-4 rounded shadow-md">
            <h3 className="font-semibold mb-2">Upload Payment File</h3>
            <input type="file" ref={fileInputRef} onChange={(e) => setFile(e.target.files[0])} />
            <button
                onClick={upload}
                className="ml-2 bg-blue-600 text-white px-3 py-1 rounded"
                disabled={isUploading}
            >
                {isUploading ? "Uploading..." : "Upload"}
            </button>
        </div>
    );
}
