import React from 'react';
import ReactDOM from 'react-dom/client';
import axios from 'axios';
import App from './App';
import { getToken } from './utils/auth';
import './index.css';

console.log('import.meta.env.VITE_API_URL',import.meta.env.VITE_API_URL)
axios.defaults.baseURL = import.meta.env.VITE_API_URL;
axios.defaults.headers.common['Accept'] = 'application/json';

const token = getToken();
console.log('token', token)
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
