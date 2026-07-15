import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M17.2 5.63L8.6 0.86L0 5.63V32.14L16.2 41.14L32.4 32.14V23.7L40 19.48V9.86L31.4 5.08L22.8 9.86V18.3L17.2 21.41V5.63ZM38 18.3L32.4 21.41V15.25L38 12.14V18.3ZM36.94 10.44L31.4 13.52L25.86 10.44L31.4 7.37L36.94 10.44ZM24.8 18.3V12.14L30.4 15.25V21.41L24.8 18.3ZM23.8 20.03L29.34 23.11L16.2 30.41L10.66 27.33L23.8 20.03ZM7.6 27.92L15.2 32.14V38.3L2 30.97V7.92L7.6 11.03V27.92ZM8.6 9.3L3.06 6.22L8.6 3.14L14.14 6.22L8.6 9.3ZM30.4 24.81L17.2 32.14V38.3L30.4 30.97V24.81ZM9.6 11.03L15.2 7.92V22.52L9.6 25.63V11.03Z"
            />
        </svg>
    );
}
