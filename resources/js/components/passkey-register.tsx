import { usePasskeyRegister } from '@laravel/passkeys/react';
import type { SubmitEvent } from 'react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    onSuccess: () => void;
}

export default function PasskeyRegistration({ onSuccess }: Props) {
    const [name, setName] = useState(() => {
        const ua = navigator.userAgent;

        const browser = [
            { name: 'Edge', pattern: /Edg|Edge/u },
            { name: 'Opera', pattern: /OPR|Opera|OPiOS/u },
            { name: 'Firefox', pattern: /Firefox|FxiOS/u },
            { name: 'Chrome', pattern: /Chrome|CriOS/u },
            { name: 'Safari', pattern: /Safari/u },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        const os = [
            { name: 'iPhone', pattern: /iPhone/u },
            { name: 'iPad', pattern: /iPad|Macintosh(?=.*Mobile)/u },
            { name: 'Android', pattern: /Android/u },
            { name: 'Mac', pattern: /Mac/u },
            { name: 'Windows', pattern: /Windows/u },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        return [browser, os].filter(Boolean).join(' on ') || '';
    });

    const [showForm, setShowForm] = useState(false);
    const { register, isLoading, error, isSupported } = usePasskeyRegister({
        onSuccess: () => {
            setName('');
            setShowForm(false);
            onSuccess();
        },
    });

    const handleSubmit = async (e: SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!name.trim()) {
            return;
        }

        await register(name);
    };

    const handleCancel = () => {
        setShowForm(false);
        setName('');
    };

    if (!isSupported) {
        return (
            <div className="text-sm text-muted-foreground">
                Passkeys are not supported in this browser.
            </div>
        );
    }

    if (!showForm) {
        return (
            <Button
                variant="outline"
                onClick={() => {
                    setShowForm(true);
                }}
            >
                Add passkey
            </Button>
        );
    }

    return (
        <form
            onSubmit={(e) => {
                void handleSubmit(e);
            }}
            className="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
        >
            <div className="grid gap-2">
                <Label htmlFor="passkey-name">Passkey name</Label>
                <Input
                    id="passkey-name"
                    type="text"
                    value={name}
                    onChange={(e) => {
                        setName(e.target.value);
                    }}
                    placeholder="e.g., MacBook Pro, iPhone"
                    className="mt-1 block w-full border-foreground/20"
                    autoFocus
                />
                <p className="text-xs text-muted-foreground">
                    A name helps you identify this passkey later.
                </p>
            </div>

            {error !== null && error !== '' && <InputError message={error} />}

            <div className="flex gap-2">
                <Button type="submit" disabled={isLoading || !name.trim()}>
                    {isLoading ? 'Registering...' : 'Register passkey'}
                </Button>
                <Button type="button" variant="ghost" onClick={handleCancel}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}
