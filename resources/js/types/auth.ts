export interface User {
    id: string;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Auth {
    /** `null` when the request is unauthenticated. */
    user: User | null;
}

export interface Passkey {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string | null;
    last_used_at_diff: string | null;
}

export interface TwoFactorSetupData {
    svg: string;
    url: string;
}

export interface TwoFactorSecretKey {
    secretKey: string;
}
