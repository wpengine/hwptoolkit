import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss'
})
export class FooterComponent {
  @Input() title: string = 'HeadlessWP Toolkit';
  
  currentYear = new Date().getFullYear();
  
  quickLinks = [
    { label: 'Privacy Policy', href: '#' },
    { label: 'Terms of Service', href: '#' },
    { label: 'Support', href: '#' }
  ];
  
  contactInfo = {
    email: 'contact&#64;example.com',
    phone: '(555) 123-4567'
  };
}
