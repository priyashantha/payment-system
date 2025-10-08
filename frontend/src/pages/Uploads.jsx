import { useState } from "react";
import FileUpload from "../components/FileUpload";
import UploadedList from "../components/UploadedList.jsx";

export default function Uploads() {
    const [refreshKey, setRefreshKey] = useState(0);

    const handleUploadSuccess = () => {
        // increment key to force RecordList to reload
        setRefreshKey(prev => prev + 1);
    };

    return (
        <div>
            <FileUpload onUploadSuccess={handleUploadSuccess} />
            <UploadedList key={refreshKey} />
        </div>
    );
}
