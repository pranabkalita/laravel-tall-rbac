from Class.ArticleDataCollector import ArticleDataCollector

# Database configuration
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'biostation_tall'
}

email = "your_email@example.com"

# Create an instance of the CollectArticlesData class
collector = ArticleDataCollector(db_config, email)


# Run the data collection and processing
collector.run()
