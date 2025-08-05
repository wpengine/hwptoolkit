import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GraphQLService, gql } from '../../utils/graphql.service';

interface SiteSettings {
  generalSettings: {
    title: string;
  };
}
@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss',
})
export class FooterComponent {
  siteInfo = signal<{ title: string }>({ title: 'HeadlessWP Toolkit' });
  settingsLoading = signal<boolean>(false);

  currentYear = new Date().getFullYear();
  constructor(private graphqlService: GraphQLService) {}
  ngOnInit() {
    this.loadSiteSettings();
  }
  private loadSiteSettings() {
    const SETTINGS_QUERY = gql`
      query HeaderSettingsQuery {
        generalSettings {
          title
        }
      }
    `;

    this.settingsLoading.set(true);

    this.graphqlService.query<SiteSettings>(SETTINGS_QUERY, {}).subscribe({
      next: (data: SiteSettings) => {
        if (data?.generalSettings?.title) {
          this.siteInfo.set({ title: data.generalSettings.title });
        }
        this.settingsLoading.set(false);
      },
      error: (error: any) => {
        this.settingsLoading.set(false);
      },
    });
  }
}
