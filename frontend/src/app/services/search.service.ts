import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { SearchResponse } from '../models/document.model';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  search(
    query: string,
    sortBy: 'relevance' | 'date' = 'relevance',
    page: number = 1,
    limit: number = 10
  ): Observable<SearchResponse> {
    const params = new HttpParams()
      .set('q', query)
      .set('sort', sortBy)
      .set('page', page.toString())
      .set('limit', limit.toString());
    
    return this.http.get<SearchResponse>(`${this.apiUrl}/search`, { params });
  }

  getSuggestions(query: string, limit: number = 5): Observable<{ suggestions: string[] }> {
    const params = new HttpParams()
      .set('q', query)
      .set('limit', limit.toString());
    
    return this.http.get<{ suggestions: string[] }>(`${this.apiUrl}/suggestions`, { params });
  }

  getDocumentContent(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/documents/${id}`);
  }
}
