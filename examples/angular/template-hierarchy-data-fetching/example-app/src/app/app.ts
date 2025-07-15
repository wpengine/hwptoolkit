import { Component, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent, FooterComponent } from './components';
import { DynamicContentComponent } from './components/dynamic-content/dynamic-content.component';

@Component({
  selector: 'app-root',
  imports: [
    //RouterOutlet,
    HeaderComponent,
    DynamicContentComponent,
    FooterComponent,
  ],
  templateUrl: './app.html'
})
export class App {
  protected title = signal('HeadlessWP Toolkit');
}
