import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';
import { guestGuard } from './core/guards/guest.guard';

export const routes: Routes = [
  {
    path: '',
    pathMatch: 'full',
    redirectTo: 'quotes',
  },
  {
    path: '',
    loadComponent: () =>
      import('./core/layout/auth-layout/auth-layout').then((module) => module.AuthLayout),
    canActivate: [guestGuard],
    children: [
      {
        path: 'login',
        title: 'Sign in',
        loadComponent: () => import('./features/auth/login/login').then((module) => module.Login),
      },
      {
        path: 'register',
        title: 'Create account',
        loadComponent: () =>
          import('./features/auth/register/register').then((module) => module.Register),
      },
    ],
  },
  {
    path: '',
    loadComponent: () =>
      import('./core/layout/app-shell/app-shell').then((module) => module.AppShell),
    canActivate: [authGuard],
    children: [
      {
        path: 'quotes',
        title: 'My quotations',
        loadComponent: () =>
          import('./features/quotations/quotation-list/quotation-list').then(
            (module) => module.QuotationList,
          ),
      },
      {
        path: 'quotes/new',
        title: 'New quotation',
        loadComponent: () =>
          import('./features/quotations/quotation-create/quotation-create').then(
            (module) => module.QuotationCreate,
          ),
      },
      {
        path: 'quotes/:quotationId',
        title: 'Quotation details',
        loadComponent: () =>
          import('./features/quotations/quotation-detail/quotation-detail').then(
            (module) => module.QuotationDetail,
          ),
      },
    ],
  },
  {
    path: '**',
    redirectTo: 'quotes',
  },
];
