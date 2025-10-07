/**
 * Authentication utility for Payment System
 * Supports both internal users (system - admin/finance/etc) and customers.
 */

export function login(token, user, type = "user") {
    localStorage.setItem("token", token);
    localStorage.setItem("user", JSON.stringify(user));
    localStorage.setItem("auth_type", type); // "user" or "customer"
}

export function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    localStorage.removeItem("auth_type");
}

export function getToken() {
    return localStorage.getItem("token");
}

export function getUser() {
    const user = localStorage.getItem("user");
    try {
        return user ? JSON.parse(user) : null;
    } catch {
        return null;
    }
}

export function getAuthType() {
    return localStorage.getItem("auth_type"); // "user" or "customer"
}

export function isAuthenticated() {
    return !!getToken();
}

// ---- Role-Based Helpers ----

export function isSystemUser() {
    return isAuthenticated() && getAuthType() === "user";
}

export function isCustomer() {
    return isAuthenticated() && getAuthType() === "customer";
}
