import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { SearchResponse } from '../models/document.model';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  search(
    query: string,
    sortBy: 'relevance' | 'date' = 'relevance',
    page = 1,
    limit = 10
  ): Observable<SearchResponse> {
    const params = new HttpParams()
      .set('q', query)
      .set('sort', sortBy)
      .set('page', page.toString())
      .set('limit', limit.toString());
    
    return this.http.get<SearchResponse>(`${this.apiUrl}/search`, { params });
  }

  getSuggestions(query: string, limit = 5): Observable<{ suggestions: string[] }> {
    const params = new HttpParams()
      .set('q', query)
      .set('limit', limit.toString());
    
    return this.http.get<{ suggestions: string[] }>(`${this.apiUrl}/suggestions`, { params });
  }

  getDocumentContent(id: number): Observable<unknown> {
    return this.http.get(`${this.apiUrl}/documents/${id}`);
  }
}
