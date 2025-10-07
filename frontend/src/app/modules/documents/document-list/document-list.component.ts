import { Component, inject, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { PageEvent } from '@angular/material/paginator';
import { Document } from '../../../models/document.model';
import { DocumentService } from '../../../services/document.service';
import { DocumentDetailsComponent } from '../document-details/document-details.component';

@Component({
  selector: 'app-document-list',
  templateUrl: './document-list.component.html',
  styleUrls: ['./document-list.component.scss']
})
export class DocumentListComponent implements OnInit {
  documents: Document[] = [];
  isLoading = false;
  error: string | null = null;
  
  currentPage = 1;
  pageSize = 10;
  totalDocuments = 0;
  totalPages = 0;

  displayedColumns: string[] = ['filename', 'type', 'size', 'date', 'actions'];

  private readonly documentService = inject(DocumentService);
  private readonly dialog = inject(MatDialog);

  ngOnInit(): void {
    this.loadDocuments();
  }

  loadDocuments(): void {
    this.isLoading = true;
    this.error = null;

    this.documentService.getDocuments(this.currentPage, this.pageSize).subscribe({
      next: (response) => {
        this.documents = response.documents;
        this.totalDocuments = response.total;
        this.totalPages = response.totalPages;
        this.isLoading = false;
      },
      error: () => {
        this.error = 'Failed to load documents. Please try again.';
        this.isLoading = false;
      }
    });
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadDocuments();
  }

  viewDocument(doc: Document): void {
    this.dialog.open(DocumentDetailsComponent, {
      width: '800px',
      data: { documentId: doc.id }
    });
  }

  downloadDocument(doc: Document): void {
    this.documentService.downloadDocument(doc.id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = doc.original_filename;
        link.click();
        window.URL.revokeObjectURL(url);
      },
      error: () => {
        alert('Failed to download document');
      }
    });
  }

  deleteDocument(doc: Document): void {
    if (!confirm(`Are you sure you want to delete "${doc.original_filename}"?`)) {
      return;
    }

    this.documentService.deleteDocument(doc.id).subscribe({
      next: () => {
        this.loadDocuments();
      },
      error: () => {
        alert('Failed to delete document');
      }
    });
  }

  formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  }

  getFileIcon(fileType: string): string {
    if (fileType.includes('pdf')) return 'picture_as_pdf';
    if (fileType.includes('word') || fileType.includes('document')) return 'description';
    if (fileType.includes('text')) return 'text_snippet';
    return 'insert_drive_file';
  }
}
