/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuthGuard, GuestGuard } from '@app/@core/guards';
import { UserResolverService } from '@app/@core/resolvers';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'authenticated',
    pathMatch: 'full',
  },

  // User authenticated
  {
    path: 'authenticated',
    loadChildren: () => import('@auth/authenticated.module').then(m => m.AuthenticatedModule),
    canActivate: [ AuthGuard ],
    resolve: {
      response: UserResolverService,
    }
  },

  // User not authenticated, proceed with authentication
  {
    path: 'auth',
    loadChildren: () => import('@guest/modules/auth/auth.module').then(m => m.AuthModule),
    canActivate: [ GuestGuard ],
  },

  // Fallback when no prior routes is matched
  { path: '**', redirectTo: 'authenticated', pathMatch: 'full' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
