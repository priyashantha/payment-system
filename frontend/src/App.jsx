import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Header from "./components/Header";
import Home from "./pages/Home";
import Login from "./pages/Login";
import Uploads from "./pages/Uploads.jsx";
import UploadPayments from "./pages/UploadPayments.jsx";
import Invoices from "./pages/Invoices";
import InvoiceDetail from "./pages/InvoiceDetail";
import ProtectedRoute from "./components/ProtectedRoute";
import { containerClass } from "./utils/classes";
import {isAuthenticated, isSystemUser} from "./utils/auth.jsx";

function App() {
    return (
        <Router>
            <Header />
            <main className={containerClass + " py-6"}>
                <Routes>
                    <Route path="/" element={<Home />} />
                    <Route path="/login" element={<Login />} />
                    <Route
                        path="/uploads"
                        element={
                            <ProtectedRoute when={isSystemUser()}>
                                <Uploads />
                            </ProtectedRoute>
                        }
                    />
                    <Route
                        path="/uploads/:id"
                        element={
                            <ProtectedRoute when={isSystemUser()}>
                                <UploadPayments />
                            </ProtectedRoute>
                        }
                    />
                    <Route
                        path="/invoices"
                        element={
                            <ProtectedRoute when={isAuthenticated()}>
                                <Invoices />
                            </ProtectedRoute>
                        }
                    />
                    <Route
                        path="/invoices/:id"
                        element={
                            <ProtectedRoute when={isAuthenticated()}>
                                <InvoiceDetail />
                            </ProtectedRoute>
                        }
                    />
                </Routes>
            </main>
        </Router>
    );
}

export default App;
