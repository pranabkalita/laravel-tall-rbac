class XmlService:
    def prepare_for_processing(self, data):
        results = []

        if 'PubmedArticle' in data and len(data['PubmedArticle']) > 0:
            pubmed_articles = data['PubmedArticle']

            # Check if it's a single PubmedArticle (not an indexed array)
            if isinstance(pubmed_articles, dict) and 'MedlineCitation' in pubmed_articles and 'PubmedData' in pubmed_articles:
                pubmed_articles = [pubmed_articles]
                print(pubmed_articles)

            for article in pubmed_articles:
                if 'MedlineCitation' in article and 'PubmedData' in article:
                    results.append({
                        'MedlineCitation': article['MedlineCitation'],
                        'PubmedData': article['PubmedData'],
                    })

        elif 'PubmedBookArticle' in data and len(data['PubmedBookArticle']) > 0:
            pubmed_articles = data['PubmedBookArticle']

            # Check if it's a single PubmedBookArticle (not an indexed array)
            if isinstance(pubmed_articles, dict) and 'BookDocument' in pubmed_articles and 'PubmedBookData' in pubmed_articles:
                pubmed_articles = [pubmed_articles]

            for article in pubmed_articles:
                if 'BookDocument' in article and 'PubmedBookData' in article:
                    results.append({
                        'MedlineCitation': article['BookDocument'],
                        'PubmedData': article['PubmedBookData'],
                    })

        return results

    def extract_abstract(self, element):
        abstract = ''
        if 'MedlineCitation' in element and 'Article' in element['MedlineCitation'] and 'Abstract' in element['MedlineCitation']['Article']:
            abstract_text = element['MedlineCitation']['Article']['Abstract']['AbstractText']
            if isinstance(abstract_text, list):
                abstract = ' '.join(abstract_text)
            else:
                abstract = abstract_text

        return abstract
