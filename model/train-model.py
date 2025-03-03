import pandas as pd
from sklearn.preprocessing import OneHotEncoder
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error
import joblib

# Charger le dataset
df = pd.read_csv("terrains_agricoles_tunisie_cheaper.csv")

# Séparer les features catégoriques
categorical_features = ["typeSol", "adresse"]
encoder = OneHotEncoder(handle_unknown="ignore", sparse_output=False)

# Encoder les colonnes catégorielles
encoded_features = encoder.fit_transform(df[categorical_features])

# Convertir en DataFrame
encoded_df = pd.DataFrame(encoded_features, columns=encoder.get_feature_names_out(categorical_features))

# Remplacer les anciennes colonnes par les nouvelles
df = df.drop(columns=categorical_features)
df = pd.concat([df, encoded_df], axis=1)

# Sauvegarder l’encodeur pour utilisation future
joblib.dump(encoder, "onehot_encoder.pkl")

# Définir X et y
X = df.drop("prix", axis=1)
y = df["prix"]

# Séparer les données en train/test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Initialiser et entraîner le modèle
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Évaluer le modèle
y_pred = model.predict(X_test)
mae = mean_absolute_error(y_test, y_pred)
print(f"Erreur absolue moyenne : {mae}")

# Sauvegarder le modèle
joblib.dump(model, "terrain_price_model.pkl")
