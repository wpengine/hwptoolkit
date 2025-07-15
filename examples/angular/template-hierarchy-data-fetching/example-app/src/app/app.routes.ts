import { Routes } from '@angular/router';
import { DynamicContentComponent } from './components/dynamic-content/dynamic-content.component';

export const routes: Routes = [
  { path: '**', component: DynamicContentComponent }
];