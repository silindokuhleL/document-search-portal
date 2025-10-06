import { Component, ViewChild } from '@angular/core';
import { DocumentListComponent } from './modules/documents/document-list/document-list.component';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  @ViewChild(DocumentListComponent) documentList?: DocumentListComponent;
  
  title = 'Document Search';
  activeTab = 0;

  onUploadComplete(): void {
    // Refresh document list when upload completes
    if (this.documentList) {
      this.documentList.loadDocuments();
    }
  }

  onTabChange(index: number): void {
    this.activeTab = index;
  }
}
