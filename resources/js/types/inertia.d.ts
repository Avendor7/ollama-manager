import { PageProps as InertiaPageProps } from '@inertiajs/core';

declare module '@inertiajs/core' {
  interface PageProps {
    auth: {
      user: {
        id: number;
        name: string;
        email: string;
        // Add other user properties as needed
      } | null;
    };
    // Add other global shared props here
  }
}

export {};
