import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

interface StatTileProps {
    label: string;
    value: number | string;
    description: string;
    href?: string;
    cta?: string;
    className?: string;
}

export function StatTile({ label, value, description, href, cta, className }: StatTileProps) {
    return (
        <div
            className={cn(
                'border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-start justify-center gap-1 overflow-hidden rounded-xl border p-4',
                className,
            )}
        >
            <p className="text-sm font-medium">{label}</p>
            <p className="text-2xl font-semibold">{value}</p>
            <p className="text-muted-foreground text-sm">{description}</p>
            {href && cta && (
                <Button asChild size="sm" variant="outline" className="mt-1">
                    <Link href={href}>{cta}</Link>
                </Button>
            )}
        </div>
    );
}
