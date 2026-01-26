#!/usr/bin/env python3
"""
HyperMon Proxy Server

This server acts as a proxy to fetch data from AllStarLink websites
and provide it to the web interface without CORS issues.

Features:
- Fetches currently keyed nodes from stats.allstarlink.org
- Searches AllStarLink node database
- Provides JSON API endpoints for web interface
- Bypasses CORS restrictions

Author: KI9NG
License: MIT
"""

from flask import Flask, jsonify, request
from flask_cors import CORS
import requests
from bs4 import BeautifulSoup
import re

# Initialize Flask application
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# AllStarLink website URLs
KEYED_STATS_URL = "https://stats.allstarlink.org/stats/keyed"
NODELIST_SEARCH_URL = "https://www.allstarlink.org/nodelist/"

@app.route('/api/keyed-nodes', methods=['GET'])
def get_keyed_nodes():
    """
    Fetch currently keyed nodes from AllStarLink stats
    
    Returns:
        JSON response containing:
        - success: boolean indicating if request succeeded
        - nodes: array of node objects with node, callsign, location, description, status
        - count: number of nodes returned
        
    Example response:
    {
        "success": true,
        "nodes": [
            {
                "node": "12345",
                "callsign": "W1ABC",
                "location": "Boston, MA",
                "description": "Repeater",
                "status": "keyed"
            }
        ],
        "count": 1
    }
    """
    try:
        # Set user agent to mimic browser request
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        
        # Fetch keyed nodes page
        response = requests.get(KEYED_STATS_URL, headers=headers, timeout=10)
        response.raise_for_status()
        
        # Parse HTML with BeautifulSoup
        soup = BeautifulSoup(response.text, 'html.parser')
        nodes = []
        
        # Parse the table rows - adjust selectors based on actual page structure
        table = soup.find('table')
        if table:
            rows = table.find_all('tr')[1:]  # Skip header row
            
            for row in rows:
                cols = row.find_all('td')
                if len(cols) >= 3:
                    node_data = {
                        'node': cols[0].text.strip(),
                        'callsign': cols[1].text.strip() if len(cols) > 1 else '',
                        'location': cols[2].text.strip() if len(cols) > 2 else '',
                        'description': cols[3].text.strip() if len(cols) > 3 else '',
                        'status': 'keyed'
                    }
                    nodes.append(node_data)
        
        return jsonify({
            'success': True,
            'nodes': nodes,
            'count': len(nodes)
        })
        
    except Exception as e:
        # Return error response with details
        return jsonify({
            'success': False,
            'error': str(e),
            'nodes': []
        }), 500

@app.route('/api/search-nodes', methods=['GET'])
def search_nodes():
    """
    Search for nodes by callsign or node number
    
    Query Parameters:
        q: Search term (callsign or node number)
        
    Returns:
        JSON response containing:
        - success: boolean indicating if request succeeded
        - results: array of matching node objects
        - count: number of results
        - search_term: the search term used
        
    Example request:
        GET /api/search-nodes?q=W1ABC
        
    Example response:
    {
        "success": true,
        "results": [
            {
                "node": "12345",
                "callsign": "W1ABC",
                "location": "Boston, MA",
                "description": "Repeater"
            }
        ],
        "count": 1,
        "search_term": "W1ABC"
    }
    """
    try:
        # Get search term from query parameters
        search_term = request.args.get('q', '').strip().upper()
        
        if not search_term:
            return jsonify({
                'success': False,
                'error': 'Search term required',
                'results': []
            }), 400
        
        # Set user agent header
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        
        # Make request to AllStarLink nodelist search
        params = {'search': search_term}
        response = requests.get(NODELIST_SEARCH_URL, params=params, headers=headers, timeout=10)
        response.raise_for_status()
        
        # Parse search results HTML
        soup = BeautifulSoup(response.text, 'html.parser')
        results = []
        
        # Parse search results - adjust selectors based on actual page structure
        table = soup.find('table')
        if table:
            rows = table.find_all('tr')[1:]  # Skip header row
            
            for row in rows:
                cols = row.find_all('td')
                if len(cols) >= 3:
                    result = {
                        'node': cols[0].text.strip(),
                        'callsign': cols[1].text.strip() if len(cols) > 1 else '',
                        'location': cols[2].text.strip() if len(cols) > 2 else '',
                        'description': cols[3].text.strip() if len(cols) > 3 else ''
                    }
                    results.append(result)
        
        return jsonify({
            'success': True,
            'results': results,
            'count': len(results),
            'search_term': search_term
        })
        
    except Exception as e:
        # Return error response
        return jsonify({
            'success': False,
            'error': str(e),
            'results': []
        }), 500

@app.route('/api/node-info/<node_number>', methods=['GET'])
def get_node_info(node_number):
    """
    Get detailed information about a specific node
    
    Path Parameters:
        node_number: The node number to lookup
        
    Returns:
        JSON response containing:
        - success: boolean indicating if request succeeded
        - node: object with node details
        
    Example request:
        GET /api/node-info/12345
        
    Example response:
    {
        "success": true,
        "node": {
            "node": "12345",
            "callsign": "W1ABC",
            "location": "Boston, MA",
            "description": "Repeater"
        }
    }
    """
    try:
        # Set user agent header
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        
        # Search for the specific node
        params = {'search': node_number}
        response = requests.get(NODELIST_SEARCH_URL, params=params, headers=headers, timeout=10)
        response.raise_for_status()
        
        # Parse node information
        soup = BeautifulSoup(response.text, 'html.parser')
        
        table = soup.find('table')
        if table:
            # Try to find row with matching node number
            row = table.find('tr', attrs={'data-node': node_number}) or table.find_all('tr')[1]
            if row:
                cols = row.find_all('td')
                if len(cols) >= 3:
                    node_info = {
                        'node': cols[0].text.strip(),
                        'callsign': cols[1].text.strip() if len(cols) > 1 else '',
                        'location': cols[2].text.strip() if len(cols) > 2 else '',
                        'description': cols[3].text.strip() if len(cols) > 3 else ''
                    }
                    return jsonify({
                        'success': True,
                        'node': node_info
                    })
        
        # Node not found
        return jsonify({
            'success': False,
            'error': 'Node not found'
        }), 404
        
    except Exception as e:
        # Return error response
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/health', methods=['GET'])
def health_check():
    """
    Health check endpoint
    
    Returns:
        JSON response with status and service name
        
    Example response:
    {
        "status": "healthy",
        "service": "HyperMon Proxy"
    }
    """
    return jsonify({
        'status': 'healthy',
        'service': 'HyperMon Proxy'
    })

@app.route('/', methods=['GET'])
@app.route('/index.html', methods=['GET'])
def serve_interface():
    """
    Serve the HyperMon web interface
    
    Returns:
        HTML file for the web interface
    """
    import os
    html_path = os.path.join(os.path.dirname(__file__), 'hypermon.html')
    try:
        with open(html_path, 'r') as f:
            return f.read(), 200, {'Content-Type': 'text/html'}
    except FileNotFoundError:
        return jsonify({
            'error': 'Web interface not found',
            'message': 'hypermon.html is missing from the installation directory'
        }), 404

if __name__ == '__main__':
    print("HyperMon Proxy Server Starting...")
    print("Listening on http://localhost:5000")
    print("Endpoints:")
    print("   - GET /api/keyed-nodes")
    print("   - GET /api/search-nodes?q=CALLSIGN")
    print("   - GET /api/node-info/<node_number>")
    print("   - GET /health")
    print("\nPress Ctrl+C to stop the server")
    
    # Run Flask development server
    # Note: For production, use a production WSGI server like gunicorn
    app.run(host='0.0.0.0', port=5000, debug=True)
