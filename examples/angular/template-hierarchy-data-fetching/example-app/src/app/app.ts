import { Component, signal } from '@angular/core';

import { HeaderComponent, FooterComponent } from './components';
import { DynamicContentComponent } from './components/dynamic-content/dynamic-content.component';

@Component({
  selector: 'app-root',
  imports: [HeaderComponent, DynamicContentComponent, FooterComponent],
  templateUrl: './app.html',
})
export class App {}
