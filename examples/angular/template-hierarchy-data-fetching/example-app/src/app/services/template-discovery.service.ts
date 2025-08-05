import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

interface TemplateInfo {
  id: string;
  path: string;
}

@Injectable({
  providedIn: 'root',
})
export class TemplateDiscoveryService {
  private apiUrl = 'http://localhost:3000/api/templates'; // Fixed endpoint

  constructor(private http: HttpClient) {}

  getAvailableTemplates(): Observable<TemplateInfo[]> {
    return this.http.get<TemplateInfo[]>(this.apiUrl);
  }
}
